<?php
    
    $token = "990187063:AAHTlwXnl5NTQOFVbIE78IYvTIlPknfzpiY";
	$lyric = "";
	$text = "";
    $chat_id = "";
    $artist = "";
    $video = "";
    $name = "";
    $last_message = "";
    $user_ask = "";
    
    ///////////////////////////////////////////////////////////////
    
    $key1 = "lyrics";
    $key2 = "music video";
    
    $keyboard = [
                        [$key1],
                        [$key2]
        ];
        
    $keyboard_options = [
                                'keyboard' => $keyboard,
                                'resize_keyboard' => true,
                                'on_time_keyboard' =>false
        ];
        
    ///////////////////////////////////////////////////////////////      
    
    
	define('DB_USER', "id10975326_modos");
	define('DB_PASSWORD', "modos1377");
	define('DB_DATABASE', "id10975326_modos");
	define('DB_SERVER', "localhost");

	$connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	
	  $input = file_get_contents("php://input");
     $input_array = json_decode($input, true); 
    
    
   if ( isset($input_array['message'])){
       $GLOBALS['text'] = $input_array['message']['text'];
       $GLOBALS['chat_id'] = $input_array['message']['chat']['id'];
   }
 
 
 switch( $GLOBALS['text']){
     case "/start" : show_menu(); break;
     case $key1 : $last_message = $key1; insert_last_message();break;
     case $key2 : $last_message = $key2; insert_last_message(); break;
     default : read_last_message();break;
 }
 
 
 if ($GLOBALS['user_ask'] == "lyrics"){
     show_lyric();
 }else{
     show_music_video();
 }
 
 function show_lyric(){
     
   
     
  	if ($stmt = $GLOBALS['connection']->prepare("SELECT lyric FROM telegram where name ='" . $GLOBALS['text'] . "'")) {

     $stmt->execute();
     $stmt->bind_result($GLOBALS['lyric']);
     if ($stmt->fetch() == null){
         $GLOBALS['lyric'] = "nothing found, try again";
     }
     
     $stmt->close();
    
  }
   
   
     
       $reply = str_split($GLOBALS['lyric'], 4096);
       $url = "https://api.telegram.org/bot" . $GLOBALS['token'] . "/sendMessage";
      
    foreach ( $reply as $rep){
             $post_params = ['chat_id' => $GLOBALS['chat_id'], 'text' => $rep];
             send_reply($url, $post_params);
         }
    }
 
 function show_music_video(){
   
    if ($stmt = $GLOBALS['connection']->prepare("SELECT artist, name, url FROM music_video WHERE name ='" .$GLOBALS['text'] . "'")){
       $stmt->execute();
       $stmt->bind_result($GLOBALS['artist'] , $GLOBALS['name'], $GLOBALS['video']);
       $stmt->fetch();
       $stmt->close();
   }
     
   $url = "https://api.telegram.org/bot" . $GLOBALS['token'] . "/sendMessage";
   $post_params = ['chat_id' => $GLOBALS['chat_id'] ,  'text' => $GLOBALS['video']];
   
   send_reply($url, $post_params);
 }
       
   
 function send_reply($url, $post_params){
      $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
   }
   
 function show_menu(){
     $json_keyboard = json_encode($GLOBALS['keyboard_options']);
     $reply = "what you want honey?";
     $url = "https://api.telegram.org/bot" . $GLOBALS['token'] . "/sendMessage";
     $post_params = ['chat_id' => $GLOBALS['chat_id'] , 'text' => $reply, 'reply_markup' => $json_keyboard];
     send_reply($url, $post_params);
 }  
 
 function insert_last_message(){
     $sql = "INSERT INTO telegram_last_message (chat_id, last_message)
            VALUES ('" . $GLOBALS['chat_id'] . "', '" . $GLOBALS['last_message'] .  "')";

mysqli_query($GLOBALS['connection'], $sql);
}

 function read_last_message(){
     $query = "SELECT last_message FROM telegram_last_message WHERE chat_id = '" . $GLOBALS['chat_id'] . "' ORDER BY id DESC LIMIT 1";
     $result = mysqli_query($GLOBALS['connection'], $query);
     
     if (mysqli_num_rows($result) > 0) {
    
        $row = mysqli_fetch_assoc($result);
        $GLOBALS['user_ask'] = $row['last_message'];
    }
 }
 
 $connection->close();
