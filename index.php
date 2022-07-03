<?php

// ver: 1.0.20220703-0

$time_start = hrtime(true);
function debug($n)
{
	header('Content-type: application/json');
	die(json_encode($n));
}

// 用户需要用http请求的网址
const RESP_HTTP_DOMIN = [
	'pixiv.net',
	'www.pixiv.net',
	'sketch.pixiv.net',
	'i.pximg.net',
	'#s.pximg.net',
	't.co',
	'imp.pixiv.net',
	'connect.facebook.net',
	'#analytics.twitter.com',
	'js.gsspcln.jp',
	'a.pixiv.org',
	'cs.gssprt.jp',
	'static.criteo.net',
	'secure-assets.rubiconproject.com',
	'pixel-apac.rubiconproject.com',
	'#www.gstatic.cn',
	'#securepubads.g.doubleclick.net',
	'#www.recaptcha.net',
	'#cdn.ampproject.org',
	'#www.google.com',
	'drive.google.com',
	'#pagead2.googlesyndication.com',
	'#secure-assets.rubiconproject.com',
	'#cr-pall.ladsp.com',
];

// 不是在使用代理就转到介绍页面
if (substr_compare(($svr = $_SERVER['SERVER_NAME']), 'pcn', 0, 3) === 0) goto novia_start;

// 脚本需要用http请求的网址
const REQ_HTTP_DOMIN = [
	't.co' => true,
];

// 用户不应接收到的响应头
const RESP_DENY_HEADER = [
	// 'Strict-Transport-Security',
	// 'Expect-CT',
	// 'CF-RAY',
	// 'Server',
	// 'CF-Cache-Status',
	// 'X-Host-Time',
	// 'Transfer-Encoding',
];

// 用户应收到的响应头
const RESP_ACCEPT_HEADER = [
	// 'Date' => true,
	'Content-Type' => true,
	'Connection' => true,
	'Vary' => true,
	'X-Host-Time' => true,
	'X-XSS-Protection' => true,
	'Set-Cookie' => true,
	'Expires' => true,
	'Cache-Control' => true,
	'Pragma' => true,
	'X-Frame-Options' => true,
	// 'CF-RAY' => true,
	// 'Server' => true,
	// 'CF-Cache-Status' => true,
];

// 脚本不应发送的请求头
const REQ_DENY_HEADER = [
	'Accept-Encoding' => true,
	'X-Rewrite-Url' => true,
];

// 脚本应发送的请求头
// const REQ_ACCEPT_HEADER = [
// 	'Accept-Language' => true,
// 	'Accept' => true,
// 	'Cache-Control' => true,
// 	'Cookie' => true,
// 	'User-Agent' => true,
// 	'Upgrade-Insecure-Requests' => true,
// 	'Connection' => true,
// 	'Host' => true,
// 	'X-Rewrite-Url' => true,
// ];

// 获取url
if (($uri = $_SERVER['QUERY_STRING']) !== '' && ($pos = strpos($uri, '&')) !== false) $uri[$pos] = '?';
$url = isset(REQ_HTTP_DOMIN[$svr]) ? 'http://' : 'https://';
$url .= "{$svr}/{$uri}";
// $url = "http://pcn.seventop.top/test.php";
$ch = curl_init($url);

// 传递数据
if ($_POST) {
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['11'=>123]));
	// debug($_POST);
}

// 伪造请求头
$hdr = [
	"CLIENT-IP: {$_SERVER['REMOTE_ADDR']}",
	"X-FORWARDED-FOR: {$_SERVER['REMOTE_ADDR']}",
	'Accept-Encoding: gzip',
];
foreach (getallheaders() as $k => $v) if (empty(REQ_DENY_HEADER[$k])) $hdr[] = "{$k}: {$v}";
curl_setopt($ch, CURLOPT_HTTPHEADER, $hdr);

// 伪造来源
$ref = pathinfo($url)['dirname'] . '/';
curl_setopt($ch, CURLOPT_REFERER, $ref);

curl_setopt($ch, CURLOPT_HEADER, true); // 获取header头
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300); // 超时时间300秒
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 直接输出字符串
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 禁止https协议验证域名
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁止https协议验证ssl安全认证证书

$dat = curl_exec($ch); // 获取数据

// 伪造响应头
$siz = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$hdr = substr($dat, 0, $siz);
$hdr = explode("\r\n", $hdr, -2);
header(array_shift($hdr));
$noE = true;
foreach ($hdr as $h) {
	$n = substr($h, 0, strpos($h, ':'));
	if ($noE) if (substr_compare($h, 'ncoding', -7, 7) === 0) $noE = false;
	if (isset(RESP_ACCEPT_HEADER[$n])) header($h);
}

$dat = substr($dat, $siz); // 获取身体
if (!$noE) $dat = gzdecode($dat);
$typ = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); // 获取mime类型

// $time_end = hrtime(true);
// setcookie('Age', (string)($time_end - $time_start));

if (strpos($typ, 'text/html') !== false) include '../sona/lib/script.html';

// 改https为http
foreach ([
	'image' => false,
	'text' => true,
	'java' => true,
	'json' => true,
	'audio' => false,
	'xml' => true,
	'video' => false,
	'form' => true,
] as $key => $val) {
	if (strpos($typ, $key) === false) continue;
	if ($val) {
		foreach (RESP_HTTP_DOMIN as $val) {
			if ($val[0] === '#' || isset(REQ_HTTP_DOMIN[$val])) continue;
			$ned[] = 'https:\\/\\/' . $val;
			$rep[] = 'http:\\/\\/' . $val;
			$ned[] = 'https://' . $val;
			$rep[] = 'http://' . $val;
		}
		exit(str_ireplace($ned, $rep, $dat));
	} else exit($dat);
}
exit($dat);
novia_start:
/*
$header = [
"GET /www/js/build/runtime.660aa7853a24ac8fc9d7.js HTTP/1.1",
"Host: s.pximg.net",
"Referer: https://www.pixiv.net/",
'Accept: *' . '/*',
'Accept-Encoding: gzip, deflate',
'Accept-Language:zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2',
'Connection:keep-alive',
'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:88.0) Gecko/20100101 Firefox/88.0',
'Cache-Control: max-age=0, no-cache',
'Pragma: no-cache/',
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
*/
function getIP()
{
	return file_get_contents('https://ifconfig.me');
}
if (!$_SERVER['QUERY_STRING']) {
	$aim = 'index';
	goto index_start;
} else if (($qry = $_SERVER['QUERY_STRING']) === 'hosts.json') {
	$txt = "\r\n#Pixiv_CN {\r\n\r\n#   Link: http://pcn.seventop.top/hosts/\r\n";
	$add = getIP();
	foreach (RESP_HTTP_DOMIN as $val) $txt .= $val[0] == '#' ? "\r\n#   {$add} " . substr($val, 1) : "\r\n    {$add} {$val}";
	$txt .= "\r\n\r\n#}\r\n\r\n";
	header('Content-type: application/json');
	exit(json_encode([
		'code' => 0,
		'info' => 'success',
		'time' => date('Y-m-d H:i:s'),
		'data' => [
			'ip' => $add,
			'list' => RESP_HTTP_DOMIN,
			'text' => $txt,
		],
	]));
} else if ($qry === 'hosts' || $qry === 'hosts/') {
	$aim = 'hosts';
	goto html_start;
} else if (substr_compare($qry, 'intro', -5, 5) === 0) {
	$dmn = $_SERVER['HTTP_HOST'];
	header('Content-type: application/json');
	header('Access-Control-Allow-Origin: http://api' . substr($dmn, strpos($dmn, '.')));
	exit(json_encode([
		'code' => 0,
		'data' => [
			'addr' => [true, ($u = "http://$dmn/") . 'hosts.json', $u . '?hosts.json'],
			'desc' => '此API可以获取Pixiv CN的hosts配置。' .
				'<br />如果您想制作自动更新hosts的脚本，这个API可能会有所帮助。',
			'mtci' => 'GET',
			'type' => 'application/json',
			'frmt' => [
				'code' => ['@' => '@', '状态代码', 0],
				'info' => ['@' => '@', '状态信息', 'success'],
				'time' => ['@' => '@', '请求时间', '1687-04-19 10:05:49'],
				'data' => [
					'ip' => ['@' => '@', '本站IP', '45.145.6.238'],
					'list' => ['@' => '@', '域名列表', ['pixiv.net', '#www.pixiv.net']],
					'text' => [
						'@' => '@', 'hosts文本',
						"\r\n#Pixiv_CN {\r\n\r\n#   Link: http://pcn.seventop.top" .
							"/hosts/\r\n\r\n    45.145.6.238 pixiv.net\r\n#   45.145.6.238 www.pixiv.net\r\n\r\n#}\r\n\r\n"
					],
				],
			],
		],
	]));
} else {
	header('Location: ../../../../../../../../');
	exit();
}
hosts_start:
?>

<!DOCTYPE html>
<html lang="ch">

<?php getHtml('hosts 与 API - '); ?>

<body>
	<h1>PCN hosts</h1>
	欲使用Pixiv CN工具，<br />
	请将下方文本粘贴进您的hosts文件。<br /><br /><br />
	<?php getHosts(); ?>
	<br />
	本站提供hosts的API服务。<br />
	<script type="text/javascript">
		function goAPI() {
			var arr = window.location.href.split('/')[2];
			arr = arr.split('.');
			arr.shift();
			window.location.href = 'http://api.' + arr.join('.') + '/pcn/hosts.json/intro/';
		}
	</script>
	详情请点击<a href="javascript: goAPI();">PCN hosts API</a>
	<br /><br /><br /><br />
</body>

</html>
<?php
exit();
index_start:
?>
<!DOCTYPE html>
<html lang="ch">

<?php getHtml(); ?>

<body>
	<img src="./logo.png" /><br />
	<h1>Pixiv<br />Crede Network<br />Nabber</h1>
	<hr color="#000000" /><br /><br />
	欢迎使用PCN。<br /><br />
	Pixiv简陋网络抓取工具（PCN）<br />
	是由个人开发的<br />
	完全免费的Pixiv代理工具。<br /><br />
	藉由修改您电脑的hosts文件，<br />
	不借助额外工具，<br />
	您就可以畅爽浏览Pixiv，<br />
	体验自由网上冲浪的绝妙感觉。<br /><br />
	<a href="https://github.com/E0SelmY4V/pcn">访问GitHub上的本项目</a><br /><br />
	<hr color="#000000" style="width: 3em;bottom: 0.5em;" /><br />
	点击按钮复制内容。<br />
	粘贴进hosts后<br />
	即可访问<a href="http://pixiv.net">http://pixiv.net</a>冲浪。<br />
	因技术限制，<br />
	请用http://代替https://来访问。<br /><br />
	若访问时出现任何问题<br />
	可尝试重新设置hosts。<br /><br />
	<?php getHosts(); ?>
	<a href='/support.html'>赞助本站</a>
</body>

</html>
<?php
exit();
html_start:
function getHtml($ttl = '')
{
?>

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= $ttl ?>Pixiv CN</title>
		<style>
			* {
				padding: 0;
				margin: 0;
				position: relative;
			}

			body {
				padding: 5%;
			}

			#getHosts {
				border: 1px solid #000;
				padding: 0.25em;
				display: inline-block;
				background: rgb(88, 243, 158);
				color: #000;
				box-shadow: 6px 6px #000;
				left: 1em;
				top: -0.5em;
			}

			#getHosts:hover {
				transform: translateX(2px) translateY(2px);
				box-shadow: 4px 4px #000;
			}

			#getHosts:active {
				transform: translateX(5px) translateY(5px);
				box-shadow: 1px 1px #000;
			}

			.code {
				white-space: pre;
				font-family: 'Ubuntu Mono', Menlo, Monaco, 'Courier New', monospace;
				background: #272822;
				color: #F8F8F2;
			}

			.s-number {
				color: #AE81FF;
			}

			.s-note {
				color: #75715E;
			}

			.s-operation {
				color: #F92772;
			}

			.s-key {
				color: #66D9EF;
			}

			.s-string {
				color: #E6DB74;
			}

			@font-face {
				font-family: 'Ubuntu-Mono';
				src: url("../../UbuntuMono-R.ttf");
			}

			.c-div {
				display: inline-block;
				border: 1px solid #000;
				padding: 0.75em;
				overflow: auto;
				max-width: 90%;
			}

			td {
				padding-left: 0.5em;
				padding-right: 0.5em;
				text-align: center;
			}
		</style>
	</head>
<?php
}
function getHosts()
{
?>
	<div id="getHosts">复制hosts</div>
	<b id="succa" style="display: none;margin-left: 3em;">成功</b><br /><br />
	<div id="hosts" class="c-div code"></div>
	<script type="text/javascript">
		IP = "<?= getIP() ?>";
		DOMIN = JSON.parse('<?= json_encode(RESP_HTTP_DOMIN) ?>');
		(function() {
			hosts.innerHTML = "<span class='s-note'>\r\n#Pixiv_CN {\r\n\r\n#   Link: http://pcn.seventop.top/hosts/\r\n";
			for (var i = 0, timer = null; i < DOMIN.length; i++) hosts.innerHTML += DOMIN[i][0] != "#" ?
				"</span>\r\n    <span class='s-key'>" + IP + "</span> <span class='s-string'>" + DOMIN[i] :
				"</span>\r\n<span class='s-note'>#   " + IP + " " + DOMIN[i].slice(1);
			hosts.innerHTML += "</span><span class='s-note'>\r\n\r\n#}\r\n\r\n</span>";
			getHosts.onclick = function() {
				var range = document.createRange();
				range.selectNode(hosts);
				var selection = window.getSelection();
				if (selection.rangeCount > 0) selection.removeAllRanges();
				selection.addRange(range);
				document.execCommand('copy');
				selection.removeAllRanges();
				succa.style.display = "inline";
				clearTimeout(timer);
				timer = setTimeout(function() {
					succa.style.display = "none";
				}, 3000);
			};
		}());
	</script><br />
<?php
}
if ($aim === 'hosts') goto hosts_start;
if ($aim === 'index') goto index_start;
