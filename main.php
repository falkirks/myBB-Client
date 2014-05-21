<?php
class mybbBot {
  private $lastlist, $lastactive, $token, $sid, $b, $u, $p;
  function __construct($url, $user, $pass) {
    $this->b = $url;
    $this->p = $pass;
    $this->u = $user;
    if (!file_get_contents($this->b)) throw new myBBException();
    if (!$this->login($this->u, $this->p)) throw new myBBException();
  }
  private function login($user, $password) {
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
  public function post($url, $msg) { //Post a new reply to a thread
    $html = new DOMDocument();
    if(strpos($url, $this->b)) $data = connect($url, null);
    else $data = connect($b . $url, null);
    $data = substr($data, strpos($data, '<form method="post" action="newreply.php'));
    $data = substr($data, 0, strpos($data, '</form>')+7);
    print($data);
    $html->loadHTML($data);
    $els = $html->getelementsbytagname('input');
    $url = $b . $html->getElementById('quick_reply_form')->getAttribute('action');
    $list = array();
    foreach($els as $inp) {
      $name = $inp->getAttribute('name');
      $list[$inp->getAttribute('name')] = $inp->getAttribute('value');
    }
    $list["message"] = $msg;
    unset($list["previewpost"]);
    $this->connect($url, $list);
  }
}

class myBBException extends Exception {}
