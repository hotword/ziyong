import { Crypto, load, _ } from './lib/cat.js';

let siteUrl = 'http://www.88kanqiu.one';
let siteKey = '';
let siteType = 0;
let headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36'
};

async function request(reqUrl, postData, agentSp, get) {

    let res = await req(reqUrl, {
        method: get ? 'get' : 'post',
        headers: headers,
        data: postData || {},
        postType: get ? '' : 'form',
    });

    let content = res.content;
    return content;
}

async function get(reqUrl) {
    let res = await req(reqUrl, {
        method: 'get',
        headers: headers
    });

    let content = res.content;
    return content;
}

async function init(cfg) {
    siteKey = cfg.skey;
    siteType = cfg.stype;
    if(cfg.ext) {
        siteUrl = cfg.ext;
    }
}

async function home(filter) {
    let classes = [{
        type_id: '0',
        type_name: '全部直播',
    },{
        type_id: '1',
        type_name: '篮球直播',
    },{
        type_id: '8',
        type_name: '足球直播',
    },{
        type_id: '29',
        type_name: '其他直播',
    }];

    let filterObj = genFilterObj();
    return JSON.stringify({
        class: classes,
        filters: filterObj
    });
}

async function category(tid, pg, filter, extend) {
    if(pg <=0) pg = 1;
    let cateId = tid == '0'?'':tid;
    if (extend['cateId']) {
        cateId = extend['cateId'];
    }
    let url = siteUrl;
    if (cateId != '') {
        url = url + '/match/' + cateId + '/live';
    }
    const html = await get(url);
    const $ = load(html);
    let videos = [];
    let cards = $('.list-group-item');
    for(let i=0;i<cards.length;i++){
        const n = cards[i];
        let vid = $($(n).find('.btn.btn-primary')[0]).attr('href');
        
        vid = vid == undefined? '':siteUrl + vid;
        let time = $($(n).find('.category-game-time')[0]).text().trim();
        let gameType = $($(n).find('.game-type')[0]).text().trim();
        let teamOne = $($(n).find('.team-name')[0]).text().trim();
        let teamTwo = $($(n).find('.team-name')[1]).text().trim();
        let liveStatus = $($(n).find('.pay-btn > a')[0]).text().trim();
        let name = '';
        if (time != '') {
            name = time + ' ' + gameType + ' ' + teamOne + ' VS ' + teamTwo + ' ' + liveStatus;
        } else {
            name = $(n).text().replaceAll('\n', '').trim();
        }
        let pic = $($(n).find('.col-xs-1 > img')[0]).attr('src');
        if(!pic || pic == '') pic = 'http://www.88kanqiu.one/static/img/default-img.png';
        if(pic.indexOf('http') < 0) pic = siteUrl + pic;
        let remark = $($(n).find('.btn.btn-primary')).text();
        if (extend['livingStatus'] != '1' || (extend['livingStatus'] === '1' && remark.length > 0)) {
            videos.push({
                vod_id: vid == ''?name:vid,
                vod_name: name,
                vod_pic: pic,
                vod_remarks: remark
            });
        }
    };
    return JSON.stringify({
        list: videos,
        page: 1,
        pagecount: 1,
        limit: 0,
        total: videos.length
    });
}

async function detail(id) {
    try {
        if(id.indexOf('http') < 0) {
            return '{}';
        }
        let url = id + '-url';
        const data = JSON.parse(await get(url));
        let playUrls = [];
        data.forEach(item => {
            let name = item['name'];
            let playUrl = item['url'];
            //技术有限，猫影视过滤掉采集不到真实地址
            if(playUrl.indexOf('sportsteam1234.com') < 0 && playUrl.indexOf('sfk=') < 0) {
                playUrls.push(name + '$' + playUrl);
            }
            
        });
        const video = {
            vod_id: id,
            vod_play_from: 'Leospring',
            vod_play_url: playUrls.join('#')
        };
        const list = [video];
        const result = { list };
        return JSON.stringify(result);
    } catch (e) {
       //console.log('err', e);
    }
    return null;
}

async function play(flag, id, flags) {
    return JSON.stringify(await getPlayObj(id));
}

async function getPlayObj(id) {
    // //前端混淆加密，技术有限
    // if(id.indexOf('sportsteam1234.com') > 0) {
    //     return {parse: 1, url: id}
    // }
    if(id.indexOf('?url=') > 0) {
        return {parse: 0, url: id.split('?url=')[1]}
    }
    if(id.indexOf('replayer') > 0) {
        let url = 'https://dszbok.com/prod-api/match/detail?mid=' + id.split('id=')[1] + '&pid=7&langtype=vi&zoneld=Asia/Shanghai';
        let playUrl = JSON.parse(await get(url)).data.matchinfo.live_urls[0].url;
        return {parse: 0, url: playUrl}
    }
    return {parse: 1, url: id,};
}


function genFilterObj() {
    return {
        '0': [{'key': 'livingStatus', 'name': '状态', 'value': [{'n': '全部', 'v': ''},{'n': '直播中', 'v': '1'}]}],
        '1': [{'key': 'cateId', 'name': '类型', 'value': [{'n': 'NBA', 'v': '1'}, {'n': 'CBA', 'v': '2'}, {'n': '篮球综合', 'v': '4'}, {'n': '纬来体育', 'v': '21'}]}],
        '8': [{'key': 'cateId', 'name': '类型', 'value': [{'n': '英超', 'v': '8'}, {'n': '西甲', 'v': '9'}, {'n': '意甲', 'v': '10'}, {'n': '欧冠', 'v': '12'}, {'n': '欧联', 'v': '13'}, {'n': '德甲', 'v': '14'}, {'n': '法甲', 'v': '15'}, {'n': '欧国联', 'v': '16'}, {'n': '足总杯', 'v': '27'}, {'n': '国王杯', 'v': '33'}, {'n': '中超', 'v': '7'}, {'n': '亚冠', 'v': '11'}, {'n': '足球综合', 'v': '23'}, {'n': '欧协联', 'v': '28'}, {'n': '美职联', 'v': '26'}]}], 
        '29': [{'key': 'cateId', 'name': '类型', 'value': [{'n': '网球', 'v': '29'}, {'n': '斯洛克', 'v': '30'}, {'n': 'MLB', 'v': '38'}, {'n': 'UFC', 'v': '32'}, {'n': 'NFL', 'v': '25'}, {'n': 'CCTV5', 'v': '18'}]}]
    }
}

export function __jsEvalReturn() {
    return {
        init: init,
        home: home,
        category: category,
        detail: detail,
        play: play,
    };
}