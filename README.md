myBB Bot Class
================

Little Class for writing bots for myBB in PHP. Won't work properly on forums with non-standard URL format.

##Example
The below example will login, create a thread (in forum 2), reply to it and then rate it with five stars.
```php
try{
  $b = new mybbBot("http://example.com/","Username","pass");
  $id = $b->newThread(2,"Thread title","Contents...");
  $b->quickReply($id,"Wow. Such a great post!");
  $b->rateThread($id,5);
}
catch(myBBException $e){
  switch ($e->getCause()) {
    case FORUM_NOT_FOUND:
      die("Forum could not be reached");
      break;
    case AUTH_ERROR:
      die("Bad login");
      break;
    default:
      die("Unknown error.");
      break;
  }
}
```

##Features
- Create new quick replies
- Create new threads (no settings)
- Rate Threads

