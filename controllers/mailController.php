<?php
class mailController
{
    
    private function createMessage($email) {
        $message = new Google_Service_Gmail_Message();
        $message->setRaw(strtr(base64_encode($email), '+/=', '-_,'));
         // $email is a raw email data string
        return $message;
    }
    
    public function sendMessage($userID, $email) {
        try {
            $msg = $this->createMessage($email);
            $this->service->users_messages->send($userID, $msg);
        }
        catch(Exception $e) {
            print 'An error occurred: ' . $e->getMessage();
        }
    }
}
