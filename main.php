?php
 set_time_limit(0);
login("Username","Password");
$b = "http://example.com/";
while(true == true){
$xml = simplexml_load_string(file_get_contents($b . "syndication.php?limit=1"))->channel->item;
if(stripos((string) $xml->description, "summon Bot") !== false && strpos(file_get_contents((string) $xml->link), "I am summonned :-)") == false) post((string) $xml->link,"I am summonned :-)");
}

function post($url,$msg){ //Post a new reply to a thread
$html= new DOMDocument();
$data = connect($url,null);
$data = substr($data, strpos($data, '<form method="post" action="newreply.php'));
$data = substr($data,0,strpos($data, '</form>')+7);
print($data);
$html->loadHTML($data);
$els = $html->getelementsbytagname('input');
$url = $b . $html->getElementById('quick_reply_form')->getAttribute('action');
$list = array();
foreach($els as $inp){
  $name = $inp->getAttribute('name');
  $list[$inp->getAttribute('name')] = $inp->getAttribute('value');
  }
  $list["message"] = $msg;
  unset($list["previewpost"]);
  connect($url,$list);
}

function connect($url,$post){ //Connector function
    global $lastlist,$lastactive,$token,$sid,$http_response_header;
	if($post != null){
    	$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                     "Cookie: mybb[referrer]=1; mybb[lastvisit]=" . $lastvist . "; mybb[lastactive]=" . $lastactive . "; loginattemps=1; mybbuser=" . $token . "; sid=" . $sid,
        'content' => http_build_query($post)
    )
  );

	}
	else{
	$opts = array('http' =>
    array(
        'method'  => 'GET',
        'header'  => 'Cookie: mybb[referrer]=1; mybb[lastvisit]=' . $lastvist . '; mybb[lastactive]=' . $lastactive . '; loginattemps=1; mybbuser=' . $token . '; sid=' . $sid
    )
  );
}
var_dump($opts);
$context  = stream_context_create($opts);
return file_get_contents($url, false, $context);
}
function login($user,$password){
    global $lastlist,$lastactive,$token,$sid,$http_response_header;
$result = connect($b . 'member.php',array(
        'username' => $user,
        'password' => $password,
        'action' => "do_login",
        'url' => ""
    )
);
$cookies = array();
foreach ($http_response_header as $hdr) {
    if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
        parse_str($matches[1], $tmp);
        $cookies += $tmp;
    }
}
var_dump($cookies);
if(!isset($cookies["mybbuser"])) die("Login failed.");
$sid = $cookies["sid"];
$token = $cookies["mybbuser"];
$lastvist = $cookies["mybb"]["lastvist"];
$lastactive = $cookies["mybb"]["lastactive"];
}
