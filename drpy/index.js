/**
 pathLib: {
  join: [Function: join],
  dirname: [Function: dirname],
  readDir: [Function (anonymous)],
  readFile: [Function (anonymous)],
  stat: [Function (anonymous)]
}
 path
 path_dir
 **/
async function main() {
  let js_order = ['360影视[官]', '菜狗[官]', '奇珍异兽[官]', '优酷[官]', '腾云驾雾[官]', '百忙无果[官]', '哔哩影视[官]'];
  let js_path = './drpy_js';
  let live_path = './lives';
  let config_path = './custom.json';
  let js_api = './drpy_libs/drpy2.min.js';
  let parse_apis = ['777,https://jx.777jiexi.com/player/?url=,0', '8090g,https://www.8090g.cn/jiexi/?url=,0','红狐,https://player.mrgaocloud.com/player/?url=,0'];
  let parses = parse_apis.map((it) => {
    let _name = it.split(',')[0];
    let _url = it.split(',')[1];
    let _type = it.split(',').length > 2 ? it.split(',')[2] : '0';
    _type = Number(_type);
    return {
      name: _name,
      url: _url,
      type: _type,
      'ext': {
        'flag': [
          'qiyi',
          'imgo',
          '爱奇艺',
          '奇艺',
          'qq',
          'qq 预告及花絮',
          '腾讯',
          'youku',
          '优酷',
          'pptv',
          'PPTV',
          'letv',
          '乐视',
          'leshi',
          'mgtv',
          '芒果',
          'sohu',
          'xigua',
          'fun',
          '风行',
        ],
      },
      'header': {
        'User-Agent': 'Mozilla/5.0',
      },
    };

  });
  let js_files = pathLib.readDir(pathLib.join(path_dir, js_path));
  // console.log(js_files);
  let live_files = pathLib.readDir(pathLib.join(path_dir, live_path));
  // console.log(live_files);
  let config_sites = [];
  try{
	  let config_file = pathLib.readFile(pathLib.join(path_dir, config_path));
	  config_sites = JSON.parse(config_file).sites;
  }catch(e){
	  console.log(`get config_file error:${e.message}`);
  }
  let channels = [];
  live_files.forEach((it) => {
    let absp = pathLib.join(path_dir, `${live_path}/${it}`).replace(/\\/g, '/');
    absp = 'http://127.0.0.1:9978/api/v1/file/' + absp.split('/zyplayer/file/')[1];
    let aname = it.split('.')[0];
    channels.push({
      'name': aname,
      'urls': [
        'proxy://do=live&type=txt&ext=' + absp,
      ],
    });
  });
  channels = channels.concat([
    {
      'name': '云星日记直播',
      'urls': [
        'proxy://do=live&type=txt&ext=http://itvbox.cc/云星日记/Ipv4.txt',
      ],
    },
    {
      'name': '本地嗅探器直播',
      'urls': [
        'proxy://do=live&type=txt&ext=http://127.0.0.1:5708/ysp',
      ],
    },
  ]);
  let json_config = {
    'wallpaper': 'https://tuapi.eees.cc/api.php?category=fengjing&type=302',
    'homepage': 'https://github.com/hjdhnx/hipy-server',
    "homeLogo":"./img/logo500x200-1.png",
    "spider": "./jar/pg.jar?md5=7633f8ea346c082b7aa163be58aed023",
    'sites': [],
    'parses': parses,
    'flags': [
      'imgo',
      'youku',
      'qq',
      'qq 预告及花絮',
      'iqiyi',
      'qiyi',
      'fun',
      'letv',
      'leshi',
      'sohu',
      'tudou',
      'xigua',
      'cntv',
      '1905',
      'pptv',
      'mgtv',
      'wasu',
      'bilibili',
      'renrenmi',
    ],
    'lives': [
      {
        'group': 'redirect',
        'channels': channels,
      },
    ],

  };
  js_files.forEach((it, index) => {
    let rname = it.replace('.js', '');
    let extra = '';
    if (rname.includes('我的哔哩传参')) {
      extra = '?type=url&params=../json/小学教育.json';
    }
	//let excludes = ['玩偶哥哥','阿里土豆'];
	let excludes = [];
	if(!excludes.includes(rname)){
    let data = {
      'key': `hipy_js_${rname}`,
      'name': `${rname}(drpy_t3)`,
      'type': 3,
      'api': js_api,
      'searchable': 1,
      'quickSearch': 1,
      'filterable': 1,
      'order_num': index,
      'ext': `${js_path}/${it}${extra}`,
    };
    json_config.sites.push(data);
	}
  });
  json_config.sites = json_config.sites.concat(config_sites);
  json_config.sites = json_config.sites.sort((a, b) => {
    let i = a.name.split('(')[0];
    let j = b.name.split('(')[0];
    return (js_order.indexOf(i) === -1 ? 9999 : js_order.indexOf(i)) - (js_order.indexOf(j) === -1 ? 9999 : js_order.indexOf(j));
  });
  return JSON.stringify(json_config,null,"\t");
}
