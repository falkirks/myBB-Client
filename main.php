<?php
define("FORUM_NOT_FOUND", 0);
define("AUTH_ERROR", 1);
class mybbBot {
  private $lastvisit, $lastactive, $token, $sid, $b, $u, $p, $h;
  function __construct($url, $user, $pass) {
    $this->b = $url;
    $this->p = $pass;
    $this->u = $user;
    if (!file_get_contents($this->b)) throw new myBBException(FORUM_NOT_FOUND);
    if (!$this->login($this->u, $this->p)) throw new myBBException(AUTH_ERROR);
  }
  private function login($user, $password) {
    global $http_response_header;
    $result = $this->connect($this->b . 'member.php', array(
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
    if (!isset($cookies["mybbuser"])) return false;
    $this->sid = $cookies["sid"];
    $this->token = $cookies["mybbuser"];
    $this->lastvist = $cookies["mybb"]["lastvist"];
    $this->lastactive = $cookies["mybb"]["lastactive"];
    return true;
  }
  private function connect($url, $post) { //Connector function
    global $http_response_header;
    if ($post != null) {
      $opts = array('http' =>
        array(
      'method'  => 'POST', 
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
        "Cookie: mybb[referrer]=1; mybb[lastvisit]=" . $this->lastvist . "; mybb[lastactive]=" . $this->lastactive . "; loginattemps=1; mybbuser=" . $this->token . "; sid=" . $this->sid, 
      'content' => http_build_query($post)
        )
        );
    }
    else {
      $opts = array('http' =>
        array(
      'method'  => 'GET', 
      'header'  => 'Cookie: mybb[referrer]=1; mybb[lastvisit]=' . $this->lastvist . '; mybb[lastactive]=' . $this->lastactive . '; loginattemps=1; mybbuser=' . $this->token . '; sid=' . $this->sid
        )
        );
    }
    return file_get_contents($url, false, stream_context_create($opts));
  }
  public function quickReply($url, $msg) { //Post a new reply to a thread at $url
    $html = new DOMDocument();
    $url = $this->switchIDToURL($url,"showthread.php?tid=");
    $data = $this->connect($url, null);
    $data = substr($data, strpos($data, '<form method="post" action="newreply.php'));
    $data = substr($data, 0, strpos($data, '</form>')+7);
    $html->loadHTML($data);
    $els = $html->getelementsbytagname('input');
    $url = $this->b . $html->getElementById('quick_reply_form')->getAttribute('action');
    $list = array();
    foreach($els as $inp) $list[$inp->getAttribute('name')] = $inp->getAttribute('value');
    $list["message"] = $msg;
    unset($list["previewpost"]);
    $data = $this->connect($url, $list);
    $data = substr($data, strpos($data,"pid=")+4);
    return substr($data, 0, strpos($data, '#'));
  }
  public function newThread($fid,$t,$c){ //Post a new thread in $id section
    $html = new DOMDocument();
    $fid = $this->switchIDToURL($fid,"newthread.php?fid=");
    $data = $this->connect($fid, null);
    if(stripos($data, "Invalid forum") !== false) return false;
    $data = substr($data, strpos($data, '<form action="newthread.php?'));
    $data = substr($data, 0, strpos($data, '</form>')+7);
    $html->loadHTML($data);
    $els = $html->getelementsbytagname('input');
    $fid .= "&processed=1";
    $list = array();
    foreach($els as $inp) $list[$inp->getAttribute('name')] = $inp->getAttribute('value');
    $list["message"] = $c;
    $list["subject"] = $t;
    unset($list["previewpost"]);
    unset($list["savedraft"]);
    unset($list["modoptions[stickthread]"]);
    unset($list["modoptions[closethread]"]);
    unset($list["postpoll"]);
    $data = $this->connect($fid, $list);
    $data = substr($data, strpos($data,"?tid=")+5);
    return substr($data, 0, strpos($data, '"'));
  }
  public function editPost($pid,$t,$c){ //Edits post with $pid
    $html = new DOMDocument();
    $pid = $this->switchIDToURL($pid,"editpost.php?pid=");
    $data = $this->connect($pid, null);
    $data = substr($data, strpos($data, '<form action="editpost.php?'));
    $data = substr($data, 0, strpos($data, '</form>')+7);
    $html->loadHTML($data);
    $els = $html->getelementsbytagname('input');
    $pid .= "&processed=1";
    foreach($els as $inp) $list[$inp->getAttribute('name')] = $inp->getAttribute('value');
    if($c != null) $list["message"] = $c;
    if($t != null) $list["subject"] = $t;
    unset($list["previewpost"]);
    $data = $this->connect($pid, $list);
  }
  public function rateThread($id,$rating){ //Rates the thread with $id
    if($rating > 5 || $rating < 1) return false;
    if($this->connect($this->b . "ratethread.php?tid=" . $id . "&rating=" . $rating . "&my_post_key=" . $this->getPostKey(),null) !== false) return true;
    return false;
  }
  public function getPostKey(){ //Get post key of logged in user
    $data = $this->connect($this->b, null);
    $data = substr($data,strpos($data,'var my_post_key = "')+19);
    return substr($data,0,strpos($data,'"'));
  }
  private function switchIDToURL($id,$url){
    if(is_numeric($id)) $id = $url . $id;
    if(strpos($id, $this->b) === false) $id = $this->b . $id;
    return $id;
  }
}
class myBBException extends Exception {
  private $c;
    function __construct($cause) {
      $this->c = $cause;
    }
    public function getCause(){
      return $this->c;
    }
}
