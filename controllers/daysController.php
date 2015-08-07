<?php
class daysController
{
    public function __construct() {
    }
    public $adminEmail = 'mmosley@droga5.com';
    public function welldaysMailer($to, $from, $from_name, $subject, $body, $gmail_service) {
        global $error;
        $mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->From = $from;
        $mail->FromName = $from_name;
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AddAddress($to);
        $mail->preSend();
        $mime = $mail->getSentMIMEMessage();
        $m = new Google_Service_Gmail_Message();
        $data = base64_encode($mime);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        
        // url safe
        $m->setRaw($data);
        $gmail_service->users_messages->send('me', $m);
    }
    
    public function myMethod($params) {
        echo '<h3>Called ' . __CLASS__ . '->' . __FUNCTION__ . '</h3>';
        echo 'Our parameters as a named array';
        echo '<br/>id: ' . $params['id'];
        echo '<br/>action: ' . $params['action'];
    }
    
    public function myMethodIndex($params) {
        echo '<h3>Called ' . __CLASS__ . '->' . __FUNCTION__ . '</h3>';
    }
    
    private function approveWellDay($params) {
        //NEEDS TO WORK WITH REPEATING EVENTS!!!!!
        $email = $params['email'];
        $DB = $params['db'];
        
        //CHECK IF THIS WELL DAY EXISTS
        
        $selectedday = $DB->fetch("SELECT * FROM welldays WHERE id = ?", $params['id']);
        
        if ($selectedday) {
            
            //IS USER THE SUPERVISOR OF THE PERSON REQUESTING THE WELLDAY?
            $welldayowner = $selectedday["user_email"];
            $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $welldayowner);
            $supervisor = $selecteduser["supervisor"];
            
            //IS USER AN ADMIN?
            $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
            $adminstatus = $selecteduser["ADMIN"];
            if ($supervisor == $email || $adminstatus == 1) {
                


                //ADD TO TOTAL OCCUPANCY
                if ($selectedday['repeat_id']!=null) {//REPEATING EVENT
                    $cdayid=$selectedday['cday_id'];
                    //$repeatid = $selectedday['repeat_id'];
                    //$repeatversion = $selectedday['repeat_version'];
                    //$occupancyInfoExists = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key = ?", array($params['id'],"occupancy_".$repeatversion."_".$repeatid));                          
                    //$updateCharityDay = $DB->execute("UPDATE welldays_charitydays_meta SET meta_value = meta_value+1 WHERE meta_key = ? AND charityday_id=?", array("occupancy_".$repeatversion."_".$repeatid,$cdayid));
                       
                } else {

                    
                    $charitydayid = $DB->fetch("SELECT cday_id FROM welldays WHERE id=?", $params['id']);
                     $cdayid=$charitydayid["cday_id"];
                   // $updateCharityDay = $DB->execute("UPDATE welldays_charitydays SET occupancy = occupancy+1 WHERE id = ?", array($cdayid));
                }
                
                $updatemydays = $DB->execute("UPDATE welldays SET status = ? WHERE id = ?", array('approved', $params['id']));
                $welldayobj = array('id' => $params['id']);
                $resparr = array('status' => "SUCCESS: WELL DAY APPROVED", 'wellday' => $welldayobj);
                $arr = array('response' => $resparr);
            } 
            else {
                $arr = array('response' => "INVALID: YOU CANNOT MODERATE THIS USER.  YOU MUST BE HIS/HER SUPERVISOR OR AN ADMIN");
            }
        } 
        else {
            $arr = array('response' => "INVALID: NOT A WELL DAY ID");
        }
        echo json_encode($arr);
    }


    private function approveTestimonial($params) {
        $email = $params['email'];
        $DB = $params['db'];
        if (isset($_GET["id"])) {
                $selectedtestimonial = $DB->fetch("SELECT * FROM welldays_testimonials WHERE id = ?", $_GET["id"]);
        
            if ($selectedtestimonial) {
                
                ///IS USER AN ADMIN?
                $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
                $adminstatus = $selecteduser["ADMIN"];
                if ($adminstatus == 1) {
                    
                    $updatemydays = $DB->execute("UPDATE welldays_testimonials SET status = ? WHERE id = ?", array('approved', $_GET["id"]));
                    
                    $welldayobj = array('id' => $_GET["id"]);
                    $resparr = array('status' => "SUCCESS: TESTIMONIAL APPROVED", 'wellday' => $welldayobj);
                    $arr = array('response' => $resparr);
                } 
                else {
                    $arr = array('response' => "INVALID: YOU CANNOT MODERATE.  YOU MUST BE AN ADMIN");
                }
            } 
            else {
                $arr = array('response' => "INVALID: NOT A TESTIMONIAL ID");
            }
        } else {

                $arr = array('response' => "INVALID: ID REQUIRED");
                
        }
        //CHECK IF THIS WELL DAY EXISTS
        
        
        echo json_encode($arr);
    }

    private function rejectTestimonial($params) {
        $email = $params['email'];
        $DB = $params['db'];
        if (isset($_GET["id"])) {
                $selectedtestimonial = $DB->fetch("SELECT * FROM welldays_testimonials WHERE id = ?", $_GET["id"]);
        
            if ($selectedtestimonial) {
                
                ///IS USER AN ADMIN?
                $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
                $adminstatus = $selecteduser["ADMIN"];
                if ($adminstatus == 1) {
                    
                    //remove WELL DAY
                    $updatemydays = $DB->execute("UPDATE welldays_testimonials SET status = ? WHERE id = ?", array('rejected', $_GET["id"]));
                    
                    $welldayobj = array('id' => $_GET["id"]);
                    $resparr = array('status' => "SUCCESS: TESTIMONIAL REJECTED", 'wellday' => $welldayobj);
                    $arr = array('response' => $resparr);
                } 
                else {
                    $arr = array('response' => "INVALID: YOU CANNOT MODERATE.  YOU MUST BE AN ADMIN");
                }
            } 
            else {
                $arr = array('response' => "INVALID: NOT A TESTIMONIAL ID");
            }
        } else {

                $arr = array('response' => "INVALID: ID REQUIRED");
                
        }
        //CHECK IF THIS WELL DAY EXISTS
        
        
        echo json_encode($arr);
    }

    private function rejectWellDay($params) {
        $email = $params['email'];
        $DB = $params['db'];
        
        //CHECK IF THIS WELL DAY EXISTS
        
        $selectedday = $DB->fetch("SELECT * FROM welldays WHERE id = ?", $params['id']);
        
        if ($selectedday) {
            
            //IS USER THE SUPERVISOR OF THE PERSON REQUESTING THE WELLDAY?
            $welldayowner = $selectedday["user_email"];
            $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $welldayowner);
            $supervisor = $selecteduser["supervisor"];
            
            //IS USER AN ADMIN?
            $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
            $adminstatus = $selecteduser["ADMIN"];
            if ($supervisor == $email || $adminstatus == 1) {
                
                //remove WELL DAY
                $updatemydays = $DB->execute("UPDATE welldays SET status = ? WHERE id = ?", array('rejected', $params['id']));
                
                


                                //REDUCE TOTAL OCCUPANCY
                if ($selectedday['repeat_id']!=null) {//REPEATING EVENT
                    $cdayid=$selectedday['cday_id'];
                    $repeatid = $selectedday['repeat_id'];
                    $repeatversion = $selectedday['repeat_version'];
                    $updateCharityDay = $DB->execute("UPDATE welldays_charitydays_meta SET meta_value = meta_value-1 WHERE meta_key = ? AND charityday_id=?", array("occupancy_".$repeatversion."_".$repeatid,$cdayid));
                       
                } else {

                    
                    $charitydayid = $DB->fetch("SELECT cday_id FROM welldays WHERE id=?", $params['id']);
                     $cdayid=$charitydayid["cday_id"];
                    $updateCharityDay = $DB->execute("UPDATE welldays_charitydays SET occupancy = occupancy-1 WHERE id = ?", array($cdayid));
                }
                
                //ADD TO NUMBER OF DAYS AVAILABLE
                $updatemydays = $DB->execute("UPDATE welldays_users SET days_available = days_available+1 WHERE user_email = ?", array($welldayowner));
                $welldayobj = array('id' => $params['id']);
                $resparr = array('status' => "SUCCESS: WELL DAY REJECTED", 'wellday' => $welldayobj);
                $arr = array('response' => $resparr);
            } 
            else {
                $arr = array('response' => "INVALID: YOU CANNOT MODERATE THIS USER.  YOU MUST BE HIS/HER SUPERVISOR OR AN ADMIN");
            }
        } 
        else {
            $arr = array('response' => "INVALID: NOT A WELL DAY ID");
        }
        echo json_encode($arr);
    }

    private function addTestimonial($params) {
        $email = $params['email'];
        $DB = $params['db'];
        $selecteduser = $DB->fetch("SELECT welldays_users.*,wp_users.display_name FROM welldays_users LEFT JOIN wp_users ON welldays_users.user_email = wp_users.user_email WHERE welldays_users.user_email = ?", $email);
        $displayname = $selecteduser["display_name"];
        $newrecord = $DB->execute("INSERT INTO welldays_testimonials (user_nick, testimonial, user_email) VALUES (?, ?, ?)", array($_POST['user_nick'],$_POST['testimonial'], $email));
        $addedid = $DB->lastInsertId();  
        $welldayobj = array('id' => $addedid);
                    
        $arr = array('status' => "SUCCESS: TESTIMONIAL ADDED", 'wellday' => $welldayobj);  
        echo json_encode($arr);                          
    }
    private function emailRequest($supervisor,$email, $displayname, $params) {
        /*NEED TO SEND EMAIL TO SUPERVISOR!*/
                            if (is_null($supervisor)) {
                                $supervisor = $this->adminEmail;
                            }
                            $this->welldaysMailer($supervisor, $email, $displayname, $displayname . " WANTS A WELL DAY!", $displayname . " has requested a well day.  The details are blah blah... Please click here to moderate.", $params['gmail_service']);
                        
    }
    private function addWellDay($params) {
        $email = $params['email'];
        $DB = $params['db'];

        //CHECK IF THIS CHARITY DAY EXISTS
        //$params['id'] is the Charity Day ID
        $selectedday = $DB->fetch("SELECT * FROM welldays_charitydays WHERE id = ?", $params['id']);
        
        if ($selectedday) {
            $startdatetime=new DateTime($selectedday['day_date']);
            //CHECK IF THE USER HAS DAYS AVAILABLE
            $selecteduser = $DB->fetch("SELECT welldays_users.*,wp_users.display_name FROM welldays_users LEFT JOIN wp_users ON welldays_users.user_email = wp_users.user_email WHERE welldays_users.user_email = ?", $email);
            $daysavailable = $selecteduser["days_available"];
            $displayname = $selecteduser["display_name"];
            $supervisor = $selecteduser["supervisor"];
            if ($daysavailable > 0) {
                
                $isRepeat=false;
                if (strrpos($params['id'], "_")) {//THIS IS A REPEATING CHARITYDAY -- WE NEED TO KNOW WHICH ONE WAS SELECTED
                    $isRepeat=true;
                    $repeatarray = explode("_", $params['id']);
                    $repeatid = $repeatarray[1];
                    $repeatversion = $repeatarray[2];
                    //NOW CHECK IF USER ALREADY HAS THIS CHARITY DAY ID AS A WELL DAY
                    $thisDayExists = $DB->fetch("SELECT * FROM welldays WHERE cday_id = ? AND user_email = ? AND repeat_id = ? AND repeat_version = ? AND status != 'rejected'", array($params['id'], $email, $repeatid,$repeatversion));
               
                    //GET THE META INFO TO FIND OUT THE DATE OF THIS EVENT
                    $metainfo = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key='repeat_interval_".$repeatversion."'", array($params['id']));


                    if ($metainfo) {
                                $startdatetime=new DateTime($metainfo[0]["meta_datetime"]);
                                if ($metainfo[0]["meta_value"]=="daily") { 
                                        $startdatetime->add(new DateInterval('P'.$repeatid.'D'));
                                } else if ($metainfo[0]["meta_value"]=="weekly") {  
                                        $startdatetime->add(new DateInterval('P'.(string)(7*intval($repeatid)).'D'));
                                }                 
                    }



                } else {
                    //NOW CHECK IF USER ALREADY HAS THIS CHARITY DAY ID AS A WELL DAY
                    $thisDayExists = $DB->fetch("SELECT * FROM welldays WHERE cday_id = ? AND user_email = ? AND status != 'rejected'", array($params['id'], $email));
                }

                if ($thisDayExists) {
                    $resparr = array('description' => "USER HAS ALREADY REQUESTED THIS WELLDAY", 'success' => false);
                    $arr = array('response' => $resparr);
                } else {
                    //NEED TO CHECK IF SUBMISSION DEADLINE HAS PASSED!!!!!
                    //NOTE:  Capacity of zero is the same as unlimited.
                    if ($isRepeat==false) {
                        if ($selectedday["capacity"]!=0 && $selectedday["capacity"]!=null && $selectedday["occupancy"] >= $selectedday["capacity"] ) {
                            $resparr = array('description' => "EVENT IS AT FULL OCCUPANCY", 'success' => false);
                            $arr = array('response' => $resparr);
                        } else {
                            //ADD WELLDAY
                            $newrecord = $DB->execute("INSERT INTO welldays (cday_id, user_email) VALUES (?, ?)", array($params['id'], $email));
                            $addedid = $DB->lastInsertId();
                            //ADD TO TOTAL OCCUPANCY
                            $updateCharityDay = $DB->execute("UPDATE welldays_charitydays SET occupancy = occupancy+1 WHERE id = ?", array($params['id']));
                        }
                    } else {
                                $occupancyInfoExists = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key = ?", array($params['id'],"occupancy_".$repeatversion."_".$repeatid));                          
                                //ADD A WELL DAY
                                $query=$this->interpolateQuery("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key = ?", array($params['id'],"occupancy_".$repeatversion."_".$repeatid));
                                if ($occupancyInfoExists) {
                                    $occupancy=$occupancyInfoExists[0]["meta_value"];
                                    if ($selectedday["capacity"]!=0 && $selectedday["capacity"]!=null && $occupancy >=$selectedday["capacity"] ) {
                                        $resparr = array('description' => "EVENT IS AT FULL OCCUPANCY", 'success' => false);
                                        $arr = array('response' => $resparr);
                                    } else {
                                        //ADD TO TOTAL OCCUPANCY
                                        $updateCharityDayMeta = $DB->fetchAll("UPDATE welldays_charitydays_meta SET meta_value = meta_value+1 WHERE meta_key = ?", array("occupancy_".$repeatversion."_".$repeatid));
                                    }
                                } else {
                                    //ADD A ROW FOR OCCUPANCY FOR THIS PARTICULAR REPEATED WELLDAY
                                    $updateCharityDayMeta = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`) VALUES (?,?,?)", array($params['id'], 'occupancy_'.$repeatversion.'_'.$repeatid,1));
                                }


                        if (!isset($arr)) {
                            //ADD WELLDAY
                             $newrecord = $DB->execute("INSERT INTO welldays (cday_id, user_email, repeat_id, repeat_version) VALUES (?, ?,?,?)", array($params['id'], $email,$repeatid,$repeatversion));
                            $addedid = $DB->lastInsertId();
                        } 
                    }

                         if (!isset($arr)) {
                            
                            
                            //REDUCE NUMBER OF DAYS AVAILABLE
                            $updatemydays = $DB->execute("UPDATE welldays_users SET days_available = ? WHERE user_email = ?", array($daysavailable - 1, $email));
                            $welldayobj = array('id' => $addedid, 'charitydayid' => $params['id']);
                            $startdate=$startdatetime->format('m-d-Y');
                            $resparr = array('description' => 'THANK YOU. Your request is pending. We just sent your '.$selectedday["day_title"].' Well Day request on '.$startdate.' to your supervisor. You’ll get an email update soon.','remaining_days_available' => $daysavailable - 1, 'success' => true, 'wellday' => $welldayobj, 'supervisor' => $supervisor);
                            $arr = array('response' => $resparr, 'SELECTEDDAY'=>$selectedday);
                            $this->emailRequest($supervisor,$email, $displayname, $params);
                        }

                }
            } 
            else {
                $resparr = array('description' => "YOU HAVE NO DAYS AVAILABLE", 'success' => false);
                $arr = array('response' => $resparr);
            }
        } 
        else {
            $resparr = array('description' => "THIS IS NOT A CHARITY ID", 'success' => false);
            $arr = array('response' => $resparr);
        }
        
        //echo "SELECT welldays_users.*,wp_users.display_name FROM welldays_users LEFT JOIN wp_users ON welldays_users.user_email = wp_users.user_email WHERE user_email = ?".$email;
        echo json_encode($arr);
    }
    private function interpolateQuery($query, $params) {
        $keys = array();
        // build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } 
            else {
                $keys[] = '/[?]/';
            }
        }
        
        $query = preg_replace($keys, $params, $query, 1, $count);
        //trigger_error('replaced '.$count.' keys');
        
        return $query;
    }
    private function editCharityDay($params) {
        $email = $params['email'];
        $DB = $params['db'];
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        if ($adminstatus == 0) {
            $arr = array('response' => "INVALID: NOT AN ADMIN", 'admin_status' => $adminstatus);
        } else {
            $newrecord = $DB->execute("UPDATE welldays_charitydays SET day_title = '?', day_desc = '?', day_date = '?', charity_id = '?', lat = '?', long = '?', day_date_end = '?', capacity = '?', submission_date_start = '?', submission_date_end = '?'' WHERE id=?", array($_POST['title'], $_POST['description'], $_POST['datetime'], $params['id'], $_POST['latitude'], $_POST['longitude'], $_POST['datetimeend'], $_POST['capacity'], $_POST['submissionstart'], $_POST['submissionend'],$params['id']));
            //$newrecord = $DB->execute("UPDATE welldays_charitydays SET repeats = '?', day_title = '?', day_desc = '?', day_date = '?', charity_id = '?', lat = '?', long = '?', day_date_end = '?', capacity = '?', submission_date_start = '?', submission_date_end = '?'' WHERE id=?", array($_POST['repeats'], $_POST['title'], $_POST['description'], $_POST['datetime'], $params['id'], $_POST['latitude'], $_POST['longitude'], $_POST['datetimeend'], $_POST['capacity'], $_POST['submissionstart'], $_POST['submissionend'],$params['id']));
            //,'query' => $this->interpolateQuery("UPDATE welldays_charitydays SET day_title = ?, day_desc = ?, day_date = ?, charity_id = ?, lat = ?, long = ?, day_date_end = ?, capacity = ?, submission_date_start = ?, submission_date_end = ? WHERE id=?",array($_POST['title'], $_POST['description'], $_POST['datetime'], $params['id'], $_POST['latitude'], $_POST['longitude'], $_POST['datetimeend'], $_POST['capacity'], $_POST['submissionstart'], $_POST['submissionend'],$params['id']))
            $id = array('id' => $params['id']);
            $arr = array('status' => "SUCCESS: CHARITY DAY UPDATED", 'id' => $id);
        }
        echo json_encode($arr);
    }
    private function deleteCharityDay($params) {
        $email = $params['email'];
        $DB = $params['db'];
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        if ($adminstatus == 0) {
            $arr = array('response' => "INVALID: NOT AN ADMIN", 'admin_status' => $adminstatus);
        } else {
            $deleterecord = $DB->execute("DELETE FROM welldays_charitydays WHERE id=?", $params['id']);
            $arr = array('status' => "SUCCESS: CHARITY DAY DELETED");
        }
        echo json_encode($arr);
    }
    private function addCharityDay($params) {
        $email = $params['email'];
        $DB = $params['db'];
        //CHECK IF USER IS AN ADMIN
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        if ($adminstatus == 0) {
            $arr = array('response' => "INVALID: NOT AN ADMIN", 'admin_status' => $adminstatus);
        } else {
            $repeats=$_POST['repeats'];
            $repeatarray=$_POST['repeatdaysarray'];
            if ($repeats=="" || $repeats=="false") {
                $repeats=null;
            }

            $newrecord = $DB->execute("INSERT INTO `welldays_charitydays` (`day_title`, `day_desc`, `day_date`, `charity_id`, `lat`, `long`, `day_date_end`, `capacity`, `submission_date_start`, `submission_date_end`) VALUES (?,?,?,?,?,?,?,?,?,?)", array($_POST['title'], $_POST['description'], $_POST['datetime'], $params['id'], $_POST['latitude'], $_POST['longitude'], $_POST['datetimeend'], $_POST['capacity'], $_POST['submissionstart'], $_POST['submissionend']));
            
            //$newrecord = $DB->execute("INSERT INTO `welldays_charitydays` (`repeats`,`day_title`, `day_desc`, `day_date`, `charity_id`, `lat`, `long`, `day_date_end`, `capacity`, `submission_date_start`, `submission_date_end`) VALUES (?,?,?,?,?,?,?,?,?,?,?)", array($repeats, $_POST['title'], $_POST['description'], $_POST['datetime'], $params['id'], $_POST['latitude'], $_POST['longitude'], $_POST['datetimeend'], $_POST['capacity'], $_POST['submissionstart'], $_POST['submissionend']));
            $addedid = $DB->lastInsertId();
            $charitydayobj = array('charitydayid' => $addedid, 'charity' => $params['id']);
            if ($repeats==true) {
                        //$testarr = array();
                $dayofweek=intval(date('w', strtotime( $_POST['datetime'])));
                if ($repeatarray) {
                    foreach ($repeatarray as $repeatday) {
                        $startdatetime=new DateTime($_POST['datetime']);
                        $day=intval($repeatday);
                        $daydiff=$day-$dayofweek;
                        if ($daydiff<0) {
                            $daydiff=7+$daydiff;
                        }
                        $startdatetime->add(new DateInterval('P'.(string)$daydiff.'D'));
                        $startdate=$startdatetime->format('Y-m-d H:i:s');
                        //array_push($testarr, intval($repeatday));
                            $newrecord = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`, `meta_datetime`) VALUES (?,?,?,?)", array($addedid, 'repeat_start', 'date', $startdate));
                            $metaaddedid = $DB->lastInsertId();
                            $newrecord = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`, `meta_datetime`) VALUES (?,?,?,?)", array($addedid, 'repeat_interval_'.$metaaddedid,$_POST['repeattype'], $startdate));
                            $newrecord = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`, `meta_datetime`) VALUES (?,?,?,?)", array($addedid, 'repeat_end_'.$metaaddedid,$_POST['repeatsend'], $_POST['repeatsenddatetime']));

                    }
                } else {
                    $newrecord = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`, `meta_datetime`) VALUES (?,?,?,?)", array($addedid, 'repeat_start', 'date', $_POST['datetime']));
                    $metaaddedid = $DB->lastInsertId();
                    $newrecord = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`, `meta_datetime`) VALUES (?,?,?,?)", array($addedid, 'repeat_interval_'.$metaaddedid,$_POST['repeattype'], $_POST['datetime']));
                    $newrecord = $DB->execute("INSERT INTO `welldays_charitydays_meta` (`charityday_id`, `meta_key`, `meta_value`, `meta_datetime`) VALUES (?,?,?,?)", array($addedid, 'repeat_end_'.$metaaddedid,$_POST['repeatsend'], $_POST['repeatsenddatetime']));

                }
                

                           }


            
            $resparr = array('status' => "SUCCESS: CHARITY DAY ADDED", 'charityday' => $charitydayobj,'repeats'=>$repeats);
            $arr = array('response' => $resparr);
        }       
        echo json_encode($arr);
    }
    

    
    private function addCharity($params) {
        $email = $params['email'];
        $DB = $params['db'];
        
        //CHECK IF USER IS AN ADMIN
        
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        if ($adminstatus == 0) {
            $arr = array('response' => "INVALID: NOT AN ADMIN", 'admin_status' => $adminstatus);
        } 
        else {
            $newrecord = $DB->execute("INSERT INTO welldays_charities (display_shortname, display_name, contact_email, description, web_site) VALUES (?,?, ?, ?, ?)",array($_POST['shortname'],$_POST['name'], $_POST['contact'], $_POST['description'], $_POST['url']));
            $addedid = $DB->lastInsertId();
            
            $charityobj = array('id' => $addedid);
            
            $resparr = array('status' => "SUCCESS: CHARITY ADDED", 'charity' => $charityobj);
            $arr = array('response' => $resparr);
        }
        
        echo json_encode($arr);
    }
    
    public function addUser($params) {
        $email = $params['email'];
        $DB = $params['db'];
        $wpuser = $DB->fetch("SELECT * FROM wp_users WHERE user_email = ?", $email);
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
            if (!$selecteduser) {
                $newrecord = $DB->execute("INSERT INTO welldays_users (user_email) VALUES (?)", array($email));
                
                //SEND EMAIL TO ADMIN IN ORDER TO ADD SUPERVISOR
                $displayname = ($wpuser) ? $wpuser["display_name"] : 'USER NOT IN WP DB';
                
                $this->welldaysMailer($this->adminEmail, $email, $displayname, $displayname . " HAS JUST REGISTERED FOR WELLDAYS", $displayname . " NEEDS A SUPERVISOR... CLICK HERE TO ADD.", $params['gmail_service']);
            }

    }
    
    private function deleteWellDay($params) {
        $email = $params['email'];
        $DB = $params['db'];
        
        //CHECK IF THIS WELL DAY EXISTS
        
        $selectedday = $DB->fetch("SELECT * FROM welldays WHERE id = ?", $params['id']);
        
        if ($selectedday) {
            
            //CHECK IF THIS WELL DAY BELONGS TO USER
            $welldayowner = $selectedday["user_email"];
            
            //DELETE FROM `wp_hrintra`.`welldays` WHERE `id`='9';
            if ($welldayowner == $email) {
                $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
                
                $daysavailable = $selecteduser["days_available"];
                
                //remove WELL DAY
                $newrecord = $DB->execute("DELETE FROM welldays WHERE id=?", array($params['id']));
                
                //REDUCE NUMBER OF DAYS AVAILABLE
                $updatemydays = $DB->execute("UPDATE welldays_users SET days_available = ? WHERE user_email = ?", array($daysavailable + 1, $email));
                $welldayobj = array('id' => $params['id']);
                $resparr = array('remaining_days_available' => $daysavailable + 1, 'status' => "SUCCESS: WELL DAY REMOVED", 'wellday' => $welldayobj);
                $arr = array('response' => $resparr);
            } 
            else {
                $arr = array('response' => "INVALID: WELL DAY DOES NOT BELONG TO USER");
            }
        } 
        else {
            $arr = array('response' => "INVALID: NOT A WELL DAY ID");
        }
        echo json_encode($arr);
    }
    private function getTestimonial($params) {
        $email = $params['email'];
        $DB = $params['db'];
        if (isset($_GET["page"])) {
            $page = $_GET['page'];
        } else {
            $page = 0;
        }
        if (isset($_GET["limit"])) {
            $limit = $_GET['limit'];
        } else {
            $limit = 5;
        }
        if (isset($_GET["status"])) {
            $status = $_GET['status'];
            $testimonials = $DB->fetchAll("SELECT * FROM welldays_testimonials WHERE status = ? ORDER BY id DESC LIMIT ".($page*$limit).",".$limit, array($status));
        } else {
            $testimonials = $DB->fetchAll("SELECT * FROM welldays_testimonials WHERE status = 'approved' ORDER BY id DESC LIMIT ".($page*$limit).",".$limit, array());
        }
        $testimonialsWithName = $this->getUserNameForTestimonial($params['usersjson'], $testimonials);
        echo json_encode(array('response' => $testimonialsWithName));
    }

    private function getWellDayDate($params) {
        $email = $params['email'];
        $DB = $params['db'];
        $charityday = $DB->fetch("SELECT welldays.*, welldays_charitydays.day_date FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id WHERE welldays_charitydays.day_date >= CURDATE() ORDER BY welldays_charitydays.day_date ASC LIMIT 1", getdate());

        $metainfo = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key='repeat_interval_".$charityday['repeat_version']."'", array($charityday['cday_id']));


                    if ($metainfo) {
                        $startdatetime=new DateTime($metainfo[0]["meta_datetime"]);
                        if ($metainfo[0]["meta_value"]=="daily") { 
                                $startdatetime->add(new DateInterval('P'.$charityday['repeat_id'].'D'));
                        } else if ($metainfo[0]["meta_value"]=="weekly") {  
                                $startdatetime->add(new DateInterval('P'.(string)(7*intval($charityday['repeat_id'])).'D'));
                        }                 
                    } else {
                        $startdatetime=new DateTime($charityday['day_date']);
                    }



        if (!$charityday) {
            $arr = null;
        } else {
            $arr =  array('month' => $startdatetime->format('m'), 'year' => $startdatetime->format('Y'));
        }
        echo json_encode(array('response' => $arr));    
    }
    private function getWellDay($params) {
        $email = $params['email'];
        $DB = $params['db'];
                
        //CHECK IF THIS WELL DAY EXISTS
        //welldays_charitydays.submission_date_start, welldays_charitydays.submission_date_end,
        $charitydaysfields = "welldays_charitydays.day_title, welldays_charitydays.long, welldays_charitydays.lat, welldays_charitydays.capacity, welldays_charitydays.occupancy, welldays_charitydays.day_date_end, welldays_charitydays.day_desc, welldays_charitydays.charity_id, welldays_charitydays.day_date, welldays_charities.display_name,welldays_charities.special_instructions";
        if ($params['id'] == "all") {
            $charitydays = $DB->fetchAll("SELECT welldays.*, " . $charitydaysfields . " FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id WHERE welldays.user_email = ?", $email);
        } 
        else if ($params['id'] == "rejected") {
            $charitydays = $DB->fetchAll("SELECT welldays.*, " . $charitydaysfields . " FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id WHERE welldays.user_email = ? AND welldays.status = ?", array($email, 'rejected'));
        } 
        else if ($params['id'] == "pending") {
            $charitydays = $DB->fetchAll("SELECT welldays.*, " . $charitydaysfields . " FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id WHERE welldays.user_email = ? AND welldays.status = ?", array($email, 'pending'));
        } 
        else if ($params['id'] == "approved") {
            $charitydays = $DB->fetchAll("SELECT welldays.*, " . $charitydaysfields . " FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id WHERE welldays.user_email = ? AND welldays.status = ?", array($email, 'approved'));
        } 
        else {
            $charitydays = $DB->fetch("SELECT welldays.*, " . $charitydaysfields . ",welldays_charities.display_name FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id  WHERE welldays.user_email = ? AND welldays.id=?", array($email, $params['id']));
        }
        
       /* $query=$this->interpolateQuery("SELECT welldays.*, " . $charitydaysfields . " FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id WHERE welldays.user_email = ?", array($email));
        */
       $arr = array();
        
        if (!$charitydays) {
            $arr = null;
        } 
        else {
            if ($params['id'] != "all") {
                $day_array = array('id' => $charitydays['id'], 'status' => $charitydays['status'], 'title' => $charitydays['day_title'], 'description' => $charitydays['day_desc'], 'date' => $charitydays['day_date'], 'date_end' => $charitydays['day_date_end'], 'charityday_id' => $charitydays['cday_id'],'repeat_id' => $charitydays['repeat_id'],'repeat_version' => $charitydays['repeat_version'],'charity id' => $charitydays['charity_id'], 'occupancy' => $charitydays['occupancy'], 'capacity' => $charitydays['capacity'], 'latitude' => $charitydays['lat'], 'longitude' => $charitydays['long']);
                $metainfo = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key='repeat_interval_'?", array($charitydays['id'],$charitydays['repeat_version']));
        
                array_push($arr, $day_array);
            } 
            else {
                foreach ($charitydays as & $charityday) {
                    $metainfo = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key='repeat_interval_".$charityday['repeat_version']."'", array($charityday['cday_id']));
                    
                    $startdate=$charityday['day_date'];
                    $enddate=$charityday['day_date_end'];
                    //$query=$this->interpolateQuery("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key='repeat_interval_".$charityday['repeat_version']."'", array($charityday['cday_id']));
                    if ($metainfo) {
                        $startdatetime=new DateTime($metainfo[0]["meta_datetime"]);
                        $enddatetime=new DateTime($metainfo[0]["meta_datetime"]);
                        $initenddate = new DateTime($charityday['day_date_end']);
                        $initstartdate=new DateTime($charityday['day_date']);
                        $difftime = $initenddate->diff($initstartdate);
                        $interval = new DateInterval('P'.$difftime->y.'Y'.$difftime->d.'DT'.$difftime->h.'H'.$difftime->i.'M'.$difftime->s.'S');
                        $enddatetime->add($interval);
                        if ($metainfo[0]["meta_value"]=="daily") {
                                
                                $startdatetime->add(new DateInterval('P'.$charityday['repeat_id'].'D'));
                                $enddatetime->add(new DateInterval('P'.$charityday['repeat_id'].'D'));
                                $startdate=$startdatetime->format('Y-m-d H:i:s');
                                $enddate=$enddatetime->format('Y-m-d H:i:s');

                        } else if ($metainfo[0]["meta_value"]=="weekly") {
                                
                                $startdatetime->add(new DateInterval('P'.(string)(7*intval($charityday['repeat_id'])).'D'));
                                $enddatetime->add(new DateInterval('P'.(string)(7*intval($charityday['repeat_id'])).'D'));
                                $startdate=$startdatetime->format('Y-m-d H:i:s');
                                $enddate=$enddatetime->format('Y-m-d H:i:s');

                        }  
                        
                        
                        
                    }
                    $locationname = $this->getLocationName($charityday['lat'], $charityday['long']);
                    $day_array = array('id' => $charityday['id'], 'status' => $charityday['status'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $startdate, 'date_end' => $enddate, 'location_name' => $locationname,'charityday_id' => $charityday['cday_id'],'repeat_id' => $charityday['repeat_id'],'repeat_version' => $charityday['repeat_version'],'special_instructions' => $charityday['special_instructions'],'charityname' => $charityday['display_name'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                    
                    array_push($arr, $day_array); 
                }
            }
        }
        $daysavail = $DB->fetch("SELECT days_available FROM welldays_users WHERE user_email = ?", $email);
        $responsearray = array('days_available' => $daysavail, 'days' => $arr);//, 
        echo json_encode(array('response' => $responsearray));
    }
    
    private function addWellDayToCalendar($params) {
        $email = $params['email'];
        $DB = $params['db'];
        $cal_service = $params['cal_service'];
        
        //CHECK IF THIS WELL DAY EXISTS
        
        $charitydays = $DB->fetch("SELECT welldays.*, welldays_charitydays.day_title, welldays_charitydays.day_desc, welldays_charitydays.charity_id, welldays_charitydays.day_date  FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id WHERE welldays.user_email = ? AND welldays.id=?", array($email, $params['id']));
        
        $arr = array();
        if (!$charitydays) {
            $arr = array("INVALID: NO WELL DAY FOUND");
        } 
        else {
            $day_array = array('id' => $charitydays['id'], 'title' => $charitydays['day_title'], 'description' => $charitydays['day_desc'], 'date' => $charitydays['day_date'], 'date_end' => $charitydays['day_date_end'], 'charity id' => $charitydays['charity_id'], 'occupancy' => $charitydays['occupancy'], 'capacity' => $charitydays['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
            
            //array_push($arr, $day_array);
            $resparr = array('status' => "SUCCESS: WELL DAY ADDED TO CALENDAR", 'day' => $day_array);
            array_push($arr, $resparr);
        }
        
        //$calList = $cal_service->calendarList->listCalendarList()->items;
        //$date = new DateTime();
        //$date=$charitydays['day_date'];
        $date = new DateTime($charitydays['day_date']);
        $date = str_replace(" ", "T", $charitydays['day_date']) . '-07:00';
        
        //$date = strtotime($date);
        //2015-02-24 00:00:01
        $event = new Google_Service_Calendar_Event();
        $event->setSummary($charitydays['day_title']);
        $event->setLocation($charitydays['charity_id']);
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($date);
        $event->setStart($start);
        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($date);
        $event->setEnd($end);
        $attendee1 = new Google_Service_Calendar_EventAttendee();
        $attendee1->setEmail($email);
        $attendees = array($attendee1,
        );
        $event->attendees = $attendees;
        $createdEvent = $cal_service->events->insert('primary', $event);
        
        $resparr = array('dateis' => $date);
        array_push($arr, $resparr);
    
        echo json_encode(array('response' => $arr));
    }
    
    private function getCharityDay($params) {
        $DB = $params['db'];
        $charitydayscount = $DB->fetch("SELECT count(id) FROM welldays_charitydays ");
        $count = $charitydayscount["count(id)"];
                

        //NEED TO SPLIT ID, IF A REPEATER
        $isRepeat=false;
        $charitydayid = $params['id'];
        if (strrpos($params['id'], "_")) {//THIS IS A REPEATING CHARITYDAY -- WE NEED TO KNOW WHICH ONE WAS SELECTED
                    $isRepeat=true;
                    $repeatarray = explode("_", $params['id']);
                    $charitydayid = $repeatarray[0];
                    $repeatid = $repeatarray[1]; //This is the number of repeated days it is away from the beginning -- need to check
                    $repeatversion = $repeatarray[2];//THIS IS THE VERSION... WE NEED TO USE TO GET WHAT SORT OF REPEAT THIS IS 'repeat_interval_'.$repeatversion
        }
        
        if (isset($_GET["page"])) {
            $page = $_GET['page'];
        } else {
            $page = 0;
        }
        if (isset($_GET["limit"])) {
            $limit = $_GET['limit'];
        } else {
            $limit = 5;
        }


        //CHECK IF THIS WELL DAY EXISTS
        if ($params['id'] != "all") {
            if ($isRepeat) {
                $charitydays = $DB->fetch("SELECT welldays_charitydays.*, welldays_charities.display_name, welldays_charitydays_meta.meta_value  FROM welldays_charitydays LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' WHERE welldays_charitydays.id=?", array($charitydayid));
            } else {
                $charitydays = $DB->fetch("SELECT welldays_charitydays.*, welldays_charities.display_name FROM welldays_charitydays LEFT JOIN welldays_charities ON welldays_charitydays.charity_id = welldays_charities.id WHERE welldays_charitydays.id=?", array($charitydayid));   
            }
            
        } 
        else {
            if (isset($_GET["page"])) {
                $charitydays = $DB->fetchAll("SELECT * FROM welldays_charitydays LIMIT ".($page*$limit).",".$limit);
            } else {
                $charitydays = $DB->fetchAll("SELECT * FROM welldays_charitydays");
            }
        }
        
        $arr = array();
        if (!$charitydays) {
            $arr = array("NO CHARITY DAYS FOUND");
        } 
        else {
            if ($params['id'] != "all") {
                $locationname = $this->getLocationName($charitydays['lat'], $charitydays['long']);
                $approvedusers = $DB->fetchAll("SELECT user_email FROM welldays WHERE cday_id = ? AND status = 'approved'", array($params['id']));
                $pendingusers = $DB->fetchAll("SELECT user_email FROM welldays WHERE cday_id = ? AND status = 'pending'", array($params['id']));

                $metainfo = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ?", array($params['id']));
                
                $approvedcomplete = $this->getUserName($params['usersjson'], $approvedusers);
                $pendingcomplete = $this->getUserName($params['usersjson'], $pendingusers);
                $user_array = array('approved' => $approvedcomplete, 'pending' => $pendingcomplete);

                $submitdateend=$charitydays['submission_date_end'];
                $submitdatestart=$charitydays['submission_date_start'];
                $datestart=$charitydays['day_date'];
                $dateend=$charitydays['day_date_end'];

                if ($isRepeat) {
                                $startdatetime=new DateTime($charitydays['day_date']);
                                $enddatetime=new DateTime($charitydays['day_date_end']);
                               // $submitstartdatetime=new DateTime($charitydays['submission_date_start']);
                                $submitenddatetime=new DateTime($charitydays['submission_date_end']);

                                if ($charitydays["meta_value"]=="daily") { 
                                        $startdatetime->add(new DateInterval('P'.$repeatid.'D'));
                                        $enddatetime->add(new DateInterval('P'.$repeatid.'D'));
                                        //$submitstartdatetime->add(new DateInterval('P'.$repeatid.'D'));
                                        $submitenddatetime->add(new DateInterval('P'.$repeatid.'D'));
                                } else if ($charitydays["meta_value"]=="weekly") {  
                                        $startdatetime->add(new DateInterval('P'.(string)(7*intval($repeatid)).'D'));
                                        $enddatetime->add(new DateInterval('P'.(string)(7*intval($repeatid)).'D'));
                                        //$submitstartdatetime->add(new DateInterval('P'.(string)(7*intval($repeatid)).'D'));
                                        $submitenddatetime->add(new DateInterval('P'.(string)(7*intval($repeatid)).'D'));
                                }   

                                $submitdateend=$submitenddatetime->format('Y-m-d H:i:s');
                                //$submitdatestart=$enddatetime->format('Y-m-d H:i:s');
                                $datestart=$startdatetime->format('Y-m-d H:i:s');
                                $dateend=$enddatetime->format('Y-m-d H:i:s');
                }
  


                $day_array = array('users' => $user_array, 'locationname' => $locationname, 'id' => $params['id'], 'title' => $charitydays['day_title'], 'description' => $charitydays['day_desc'], 'date' => $datestart,'submit_date_start' => $submitdatestart,'submit_date_end' => $submitdateend,'date_end' => $dateend, 'charity id' => $charitydays['charity_id'],'charityname' => $charitydays['display_name'], 'occupancy' => $charitydays['occupancy'], 'capacity' => $charitydays['capacity'], 'latitude' => $charitydays['lat'], 'longitude' => $charitydays['long']);//,'repeats' => $charitydays['repeats']
                array_push($arr, $day_array);
            } 
            else {
                foreach ($charitydays as & $charityday) {
                    $locationname = $this->getLocationName($charityday['lat'], $charityday['long']);
                    $approvedusers = $DB->fetchAll("SELECT user_email FROM welldays WHERE cday_id = ? AND status = 'approved'", array($charityday['id']));
                    $pendingusers = $DB->fetchAll("SELECT user_email FROM welldays WHERE cday_id = ? AND status = 'pending'", array($charityday['id']));
                    $metainfo = $DB->fetchAll("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ?", array($charityday['id']));
                    $approvedcomplete = $this->getUserName($params['usersjson'], $approvedusers);
                    $pendingcomplete = $this->getUserName($params['usersjson'], $pendingusers);
                    $user_array = array('approved' => $approvedcomplete, 'pending' => $pendingcomplete);
                    $day_array = array('meta' => $metainfo, 'users' => $user_array, 'id' => $charityday['id'], 'locationname' => $locationname, 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $charityday['day_date'], 'date_end' => $charityday['day_date_end'],'submit_date_start' => $charityday['submission_date_start'],'submit_date_end' => $charityday['submission_date_end'], 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);//,'repeats' => $charityday['repeats']
                    array_push($arr, $day_array);
                }
            }
        }
        //$resp = array(array('count' => intval($count),array('count' => intval($count))
        echo json_encode(array('count' => intval($count),'response' => $arr));
    }
    public function getCharityDayByDate($params) {

        $year = (string)$params['year'];
        $month = (string)$params['month'];
        $email = $params['email'];
        $DB = $params['db'];
        $query="";
        $charitystr="";

        $usersjson = $params['usersjson'];
        $item=explode("@", $email);
        $jsoninfo = $this->getUserNameFromJSON($item[0], $usersjson);
        if($jsoninfo==null) {
            $jsoninfo['name']="Droga Yoga";
            $jsoninfo['image']="http://droga5.com/wp-content/uploads/1133_mmosley_1-325x240.jpg";
        }
        $jsoninfo['status'] = true;
        $endofmonthdate = new DateTime('now');
        $endofmonthdate->setDate($year, $month,1);
        $endofmonthdate->modify('last day of this month');
        $endofmonthdate->setTime(23, 59,59);
        $endofmonthdate=$endofmonthdate->format('Y-m-d H:i:s');

                $startofmonthdate = new DateTime('now');
        $startofmonthdate->setDate($year, $month,1);
        $startofmonthdate->setTime(0, 0,0);
        $startofmonthdate=$startofmonthdate->format('Y-m-d H:i:s');

        //$startof = new DateTime('now');
        //$endofmonthdate->modify('last day of this month');
        //$endofmonthdate->setTime(23, 59,59);
        //$endofmonthdate=$endofmonthdate->format('Y-m-d H:i:s');

        if (isset($_GET["charity"]) && $_GET["charity"] !="") {


$arrayval=explode(",",$_GET["charity"]);
$charitystring="";
foreach ($arrayval as & $val) {
    if ($charitystring != "") {
        $charitystring = $charitystring." OR ";   
    }
    $charitystring = $charitystring."charity_id = ".$val;
}

            // $charitydays = $DB->fetchAll("SELECT welldays_charitydays.*,welldays_charitydays_meta.meta_value,welldays_charitydays_meta.meta_datetime,welldays_charitydays_meta.meta_key 
            //     FROM welldays_charitydays LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' 
            //     WHERE (".$charitystring.") AND (MONTH(day_date)=? AND YEAR(day_date)=?)
            //     OR (
            // (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_end%' AND (meta_value = 'never' OR (meta_value = 'date' AND meta_datetime < DATE(?))))))
            // OR
            // (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_start%' AND (meta_value = 'date' AND MONTH(meta_datetime)<=? AND YEAR(meta_datetime)<=?))))
            // AND

            // (".$charitystring.")
            // )

            // ", array($month,$year,$endofmonthdate,$month,$year));


            $charitydays = $DB->fetchAll("SELECT welldays_charitydays.*,welldays_charitydays_meta.meta_value,welldays_charitydays_meta.meta_datetime,welldays_charitydays_meta.meta_key 
                FROM welldays_charitydays LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' 
                WHERE (".$charitystring.") AND (MONTH(day_date)=? AND YEAR(day_date)=?)
                OR (
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_end%' AND (meta_value = 'never' OR (meta_value = 'date' AND meta_datetime > DATE(?))))))
            AND
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_start%' AND (meta_value = 'date' AND meta_datetime < DATE(?)))))
            AND

            (".$charitystring.")
            )

            ", array($month,$year,$startofmonthdate,$endofmonthdate));

        
        } else {
            // $charitydays = $DB->fetchAll("SELECT welldays_charitydays.*,welldays_charitydays_meta.meta_value,welldays_charitydays_meta.meta_datetime,welldays_charitydays_meta.meta_key FROM welldays_charitydays LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' WHERE (MONTH(day_date)=? AND YEAR(day_date)=?) OR 
            // (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_end%' AND (meta_value = 'never' OR (meta_value = 'date' AND meta_datetime < DATE(?))))))
            // OR
            // (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_start%' AND (meta_value = 'date' AND MONTH(meta_datetime)<=? AND YEAR(meta_datetime)<=?))))
            // ", array($month,$year,$endofmonthdate,$month,$year));

                        $charitydays = $DB->fetchAll("SELECT welldays_charitydays.*,welldays_charitydays_meta.meta_value,welldays_charitydays_meta.meta_datetime,welldays_charitydays_meta.meta_key FROM welldays_charitydays LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' WHERE (MONTH(day_date)=? AND YEAR(day_date)=?) OR 
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_end%' AND (meta_value = 'never' OR (meta_value = 'date' AND meta_datetime > DATE(?))))))
            AND
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_start%' AND (meta_value = 'date' AND meta_datetime < DATE(?)))))
            ", array($month,$year,$startofmonthdate,$endofmonthdate));
        } 
        //welldays.*,welldays_charitydays.*,welldays_users.supervisor,
        

$query=$this->interpolateQuery("SELECT welldays_charitydays.*,welldays_charitydays_meta.meta_value,welldays_charitydays_meta.meta_datetime,welldays_charitydays_meta.meta_key FROM welldays_charitydays LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' WHERE (MONTH(day_date)=? AND YEAR(day_date)=?) OR 
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_end%' AND (meta_value = 'never' OR (meta_value = 'date' AND meta_datetime > DATE(?))))))
            AND
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_start%' AND (meta_value = 'date' AND meta_datetime < DATE(?)))))
            ", array($month,$year,$startofmonthdate,$endofmonthdate));

/*$query=$this->interpolateQuery("SELECT welldays_charitydays.*,welldays_charitydays_meta.meta_value,welldays_charitydays_meta.meta_datetime,welldays_charitydays_meta.meta_key FROM welldays_charitydays LEFT JOIN welldays_charitydays_meta ON welldays_charitydays_meta.charityday_id = welldays_charitydays.id AND meta_key LIKE 'repeat_interval%' WHERE (MONTH(day_date)=? AND YEAR(day_date)=?) OR 
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_end%' AND (meta_value = 'never' OR (meta_value = 'date' AND meta_datetime < DATE(?))))))
            AND
            (welldays_charitydays.id IN (SELECT charityday_id FROM welldays_charitydays_meta WHERE (meta_key LIKE 'repeat_start%' AND (meta_value = 'date' AND MONTH(meta_datetime)<=? AND YEAR(meta_datetime)<=?))))
            ", array($month,$year,$endofmonthdate,$month,$year));*/

        
        $firstdayofmonth = new DateTime();
        $firstdayofmonth->setDate($params['year'], $params['month'], 1);
        $firstdayofmonth->setTime(0, 0);
        $nbrDay = $firstdayofmonth->format('t');
        $lastdayofmonth = new DateTime();
        $lastdayofmonth->setDate($params['year'], $params['month'], $nbrDay);
        $lastdayofmonth->setTime(23, 59,59);




        //$datestr = $date->format('Y-m-d H:i:s');
        ////
        $arr = array();
        $outputarray = array();
        if (!$charitydays) {
            $arr = array("NO CHARITY DAYS FOUND FOR THIS DATE " . $month ."/".$year);
        } 
        else {
            
            foreach ($charitydays as & $charityday) {
                array_push($outputarray, $charityday['id']);
                $initdate=$charityday['day_date'];
                $initenddate = new DateTime($charityday['day_date_end']);
                $initdeadline = new DateTime($charityday['submission_date_end']);
                $initstartdate=new DateTime($charityday['day_date']);
                $startdatetime=new DateTime($charityday['meta_datetime']);
                $enddatetime=new DateTime($charityday['meta_datetime']);
                $deadline=new DateTime($charityday['meta_datetime']);
                //$repeatsid=new DateTime($charityday['meta_key']);
                if($charityday['meta_value']) {
                    $intervalval=1;
                    if($charityday['meta_value']=="weekly") {
                        $intervalval=7;
                    }
                    //REPEAT DAYS FOR DAILY EVENTS
                    $repeatarray=explode("_", $charityday['meta_key']);
                    $repeatversion=$repeatarray[2];

                    $repeatenddate = $DB->fetchAll("SELECT meta_datetime FROM welldays_charitydays_meta WHERE meta_key=? ",array("repeat_end_".$repeatversion));
                    $repeatenddatetime = new DateTime($repeatenddate[0]["meta_datetime"]);



                    $repeatcount=0;
                    
                    $diffdeadline = $initdeadline->diff($initstartdate);
                    $deadlineinterval = new DateInterval('P'.$diffdeadline->y.'Y'.$diffdeadline->d.'DT'.$diffdeadline->h.'H'.$diffdeadline->i.'M'.$diffdeadline->s.'S');
                    $deadline->add($deadlineinterval);


                    $difftime = $initenddate->diff($initstartdate);
                    $interval = new DateInterval('P'.$difftime->y.'Y'.$difftime->d.'DT'.$difftime->h.'H'.$difftime->i.'M'.$difftime->s.'S');
                    $enddatetime->add($interval);

                    

                    while ($firstdayofmonth >= $startdatetime) {
                        //GOING THROUGH REPEATED DAYS BEFORE THIS MONTH AND YEAR (TO INCREMENT THE NUMBER CORRECTLY)
                        $startdatetime->add(new DateInterval('P'.(string)$intervalval.'D'));
                        $enddatetime->add(new DateInterval('P'.(string)$intervalval.'D'));
                        $deadline->add(new DateInterval('P'.(string)$intervalval.'D'));
                        $repeatcount++;
                    }
                    while ($lastdayofmonth >= $startdatetime) {//this only works if end date never ends
                        if ($startdatetime>$repeatenddatetime ) {
                            //THIS MEANS THAT THIS DATE IS AFTER THE END OF THE REPEATING EVENT.  THEREFORE WE BREAK HERE.  NO NEED TO CONTINUE
                            break;
                        }
                        //THESE ARE REPEATED DAYS IN THIS MONTH
                        
                        $enddate=$enddatetime->format('Y-m-d H:i:s');
                        $startdate=$startdatetime->format('Y-m-d H:i:s');
                        $deadlinedate=$deadline->format('Y-m-d H:i:s');
                        //if ($lastdayofmonth > $startdatetime) {
                            //GET OCCUPANCY FOR THIS REPEATED DAY
                            
                            $occupancyInfoExists = $DB->fetch("SELECT * FROM welldays_charitydays_meta WHERE charityday_id = ? AND meta_key = ?", array($charityday['id'],"occupancy_".$repeatcount));
                            if ($occupancyInfoExists) {
                                $occupancy=$occupancyInfoExists['meta_value'];
                            } else {
                                $occupancy="0";
                            }
                            $charityreq = $DB->fetchAll("SELECT display_shortname,bgcolor FROM welldays_charities WHERE id=?",array($charityday['charity_id']));


                             $mydaysreq = $DB->fetchAll("SELECT id,status FROM welldays WHERE cday_id=? AND repeat_id=? AND user_email=? AND repeat_version=? LIMIT 1",array($charityday['id'],$repeatcount,$email,$repeatversion));

                            if ($mydaysreq) {
                                 $myday = $jsoninfo;
                                 $myday['myday_id'] = $mydaysreq[0]["id"];
                                 $myday['status'] = $mydaysreq[0]["status"];
                            } else {
                                 $myday = null;
                            }

                            $day_array = array('myday' => $myday,'charity' => $charityreq, 'submission_deadline' => $deadlinedate,'repeattype' => $charityday['meta_value'],'repeatversion' => $repeatversion, 'id' => $charityday['id']."_".$repeatcount."_".$repeatversion, 'repeats' => $charityday['meta_value'],'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $startdate, 'date_end' => $enddate, 'charity id' => $charityday['charity_id'], 'occupancy' => $occupancy, 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                            array_push($arr, $day_array);

                        $startdatetime->add(new DateInterval('P'.(string)$intervalval.'D'));
                        $enddatetime->add(new DateInterval('P'.(string)$intervalval.'D'));
                        $deadline->add(new DateInterval('P'.(string)$intervalval.'D'));
                        $repeatcount++;
                    }
                    
                } else {
                    $enddate = $charityday['day_date_end'];
                    $startdate=$charityday['day_date'];
                    $charityreq = $DB->fetchAll("SELECT display_shortname,bgcolor FROM welldays_charities WHERE id=?",array($charityday['charity_id']));
                                         $mydaysreq = $DB->fetchAll("SELECT id,status FROM welldays WHERE cday_id=? AND user_email=? LIMIT 1",array($charityday['id'],$email));

                    if ($mydaysreq) {
                         $myday = $jsoninfo;
                         $myday['myday_id'] = $mydaysreq[0]["id"];
                         $myday['status'] = $mydaysreq[0]["status"];
                    } else {
                         $myday = null;
                    }
                    // THIS IS NOT A REPEATED EVENT
                    $day_array = array('myday' => $myday,'charity' => $charityreq, 'submission_deadline' => $charityday['submission_date_end'],'id' => $charityday['id'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $startdate, 'date_end' => $enddate, 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                    array_push($arr, $day_array);
                }
                
            }
        }
        echo json_encode(array('response' => $arr,'query' => $query));//, 'outputarray' => $outputarray, 'charitystr' => $charitystr, 'charitydays' => $charitydays
    }



    public function getCharityDayForCharity($params) {
        
        //need to make this work if user passes a month and year -- NOTE -- Doing that ABOVE -- getCharityDayByDate
        if (isset($_GET["LIMIT"])) {
            $time = strtotime($_GET['LIMIT']);
            $DATELIMIT = date('Y-m-d', $time);
        } 
        else {
            $DATELIMIT = NULL;
        }
        
        //$charityid = $params['charityid'];
        $DB = $params['db'];
        
        $charitydays = $DB->fetchAll("SELECT * FROM welldays_charitydays WHERE charity_id=?", array($params['id']));
        
        $arr = array();
        if (!$charitydays) {
            $arr = array("NO CHARITY DAYS FOUND FOR THIS CHARITY " + $DATELIMIT);
        } 
        else {
            
            foreach ($charitydays as & $charityday) {
                $day_array = array('id' => $charityday['id'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $charityday['day_date'], 'date_end' => $charityday['day_date_end'], 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                array_push($arr, $day_array);
            }
        }
        echo json_encode(array('response' => $arr));
    }
    
    public function getNextCharityDayForCharity($params) {
        
        $DB = $params['db'];
        
        $charitydays = $DB->fetchAll("SELECT * FROM welldays_charitydays WHERE day_date > CURDATE() AND charity_id=? ORDER BY 'day_date' ASC LIMIT 1", array($params['id']));
        
        $arr = array();
        if (!$charitydays) {
            $arr = array("NO CHARITY DAYS FOUND FOR THIS CHARITY");
        } 
        else {
            
            foreach ($charitydays as & $charityday) {
                $day_array = array('id' => $charityday['id'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $charityday['day_date'], 'date_end' => $charityday['day_date_end'], 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                array_push($arr, $day_array);
            }
        }
        echo json_encode(array('response' => $arr));
    }

   

    public function getPrevCharityDayForCharity($params) {
        
        $DB = $params['db'];
        
        $charitydays = $DB->fetchAll("SELECT * FROM welldays_charitydays WHERE day_date < CURDATE() AND charity_id=? ORDER BY 'day_date' ASC LIMIT 1", array($params['id']));
        
        $arr = array();
        if (!$charitydays) {
            $arr = array("NO CHARITY DAYS FOUND FOR THIS CHARITY");
        } 
        else {
            
            foreach ($charitydays as & $charityday) {
                $day_array = array('id' => $charityday['id'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $charityday['day_date'], 'date_end' => $charityday['day_date_end'], 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                array_push($arr, $day_array);
            }
        }
        echo json_encode(array('response' => $arr));
    }
    public function updateTestimonial($params) {
        
        switch ($params['action']) {
            case 'add':
                $this->addTestimonial($params);
                break;

            case 'delete':
                $this->deleteTestimonial($params);
                break;

            case 'get':
                $this->getTestimonial($params);
                break;

            case 'approve':
                $this->approveTestimonial($params);
                break;

            case 'reject':
                $this->rejectTestimonial($params);
                break;


            default:
                echo "INVALID ACTION";
                break;
        }
    }
    public function updateWellDay($params) {
        
        switch ($params['action']) {
            case 'add':
                $this->addWellDay($params);
                break;

            case 'delete':
                $this->deleteWellDay($params);
                break;

            case 'get':
                $this->getWellDay($params);
                break;

            case 'date':
                $this->getWellDayDate($params);
                break;

            case 'approve':
                $this->approveWellDay($params);
                break;

            case 'reject':
                $this->rejectWellDay($params);
                break;

            case 'addToCalendar':
                $this->addWellDayToCalendar($params);
                break;

            default:
                echo "INVALID ACTION";
                break;
        }
    }
    
    public function updateCharityDay($params) {
        
        switch ($params['action']) {
            case 'get':
                $this->getCharityDay($params);
                break;

            case 'add':
                $this->addCharityDay($params);
                break;

            case 'edit':
                $this->editCharityDay($params);
                break;

            case 'delete':
                $this->deleteCharityDay($params);
                break;

            default:
                echo "INVALID ACTION";
                break;
        }
    }
    
    public function updateCharity($params) {
        
        switch ($params['action']) {
            case 'get':
                $this->getCharity($params);
                break;

            case 'add':
                $this->addCharity($params);
                break;

            default:
                echo "INVALID ACTION";
                break;
        }
    }
    public function userInfo($params) {
        
        switch ($params['action']) {
            case 'get':
                $this->getUserInfo($params);
                break;

            case 'update':
                $this->updateUserInfo($params);
                break;

            default:
                echo "INVALID ACTION";
                break;
        }
    }
    public function updateRequests($params) {
//
        $status = $params['action'];
        $email = $params['email'];
        $DB = $params['db'];
        
        $charitydayscount = $DB->fetch("SELECT count(id) FROM welldays WHERE status=? ",$status);
        $count = $charitydayscount["count(id)"];


                if (isset($_GET["page"])) {
            $page = $_GET['page'];
        } else {
            $page = 0;
        }
        if (isset($_GET["limit"])) {
            $limit = $_GET['limit'];
        } else {
            $limit = 5;
        }


        //CHECK IF USER IS AN ADMIN
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        if ($adminstatus == 1) {

            $requests = $DB->fetchAll("SELECT welldays_charitydays.*,welldays_users.supervisor,welldays_users.days_available,welldays.* FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_users ON welldays.user_email = welldays_users.user_email WHERE welldays.status = ? LIMIT ".($page*$limit).",".$limit, $status);
            $arr = $requests;
        } 
        else {
            $arr = array('admin_status' => $adminstatus);
        }
        echo json_encode(array('count' => intval($count),'response' => $arr));
    }

     public function updateMyTeam($params) {
//
        $status = $params['action'];
        $email = $params['email'];
        $DB = $params['db'];
        


                if (isset($_GET["page"])) {
            $page = $_GET['page'];
        } else {
            $page = 0;
        }
        if (isset($_GET["limit"])) {
            $limit = $_GET['limit'];
        } else {
            $limit = 5;
        }

             $charitydayscount = $DB->fetch("SELECT count(welldays.id) FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_users 
                ON welldays.user_email = welldays_users.user_email WHERE welldays_users.supervisor = ?", $email);
        $count = $charitydayscount["count(welldays.id)"];


            $requests = $DB->fetchAll("SELECT welldays_charitydays.*,welldays.*,welldays_users.supervisor,welldays_users.days_available 
                FROM welldays LEFT JOIN welldays_charitydays ON welldays.cday_id = welldays_charitydays.id LEFT JOIN welldays_users 
                ON welldays.user_email = welldays_users.user_email WHERE welldays_users.supervisor = ? ORDER BY welldays.status LIMIT ".($page*$limit).",".$limit." ", $email);
            $arr = $requests;

        echo json_encode(array('count' => intval($count),'response' => $arr));
    }
    
    /* static private $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=";
    
    public function getLocation($address){
        $url = self::$url.urlencode($address);
        
        $resp_json = self::curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);
    
        if($resp['status']='OK'){
            return $resp['results'][0]['geometry']['location'];
        }else{
            return false;
        }
    }
    
    */
    private function curl_file_get_contents($URL) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);
        
        if ($contents) return $contents;
        else return FALSE;
    }
    
    private function getLocationName($lat, $long) {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $long . "&key=AIzaSyBjVfkubXBkYj_aI6fW1Wg-4i61dT749Ng";
        $resp_json = self::curl_file_get_contents($url);
        $resp = json_decode($resp_json, true);
        
        if ($resp['status'] = 'OK' && count($resp['results']) > 0) {
            return $resp['results'][0]['formatted_address'];
            
            //return $resp['results'][0]['geometry']['location'];
            
        } 
        else {
            return 'CLICK HERE FOR LOCATION';
        }
        
        /*$curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
        $geocoder = new \Geocoder\Provider\GoogleMaps($curl);
        
        
        $locationname=$geocoder->reverse(floatval($lat),floatval($long));
        return $locationname;*/
    }
    
    private function getUserName($usersjson, $userarray) {
        $returnArray = array();
        foreach ($userarray as $user) {
            $item=explode("@", implode(" ", $user));
            $loginname = $item[0];
            $jsoninfo = $this->getUserNameFromJSON($loginname, $usersjson);
            $day_array = array('email' => $user['user_email'], 'name' => $jsoninfo['name'], 'image' => $jsoninfo['image']);
            array_push($returnArray, $day_array);
        }
        return $returnArray;
    }

    private function getUserNameForTestimonial($usersjson, $testimonialarray) {
        $returnArray = array();
        foreach ($testimonialarray as $user) {
            $item=explode( '@', $user["user_email"] ) ;
            $loginname = $item[0];
            $jsoninfo = $this->getUserNameFromJSON($loginname, $usersjson);
            $user["name"] = $jsoninfo['name'];
            $user["image"] = $jsoninfo['image'];
            //$day_array = array('email' => $user['user_email'], 'name' => $jsoninfo['name'], 'image' => $jsoninfo['image']);
            array_push($returnArray, $user);
        }
        return $returnArray;
    }
    
    private function getUserNameFromJSON($loginname, $usersjson) {
        if (!is_null($usersjson)) {
            foreach ($usersjson as $user) {
                $jsonloginname = $user['username'];
                if ($jsonloginname == $loginname) {
                    return array('name' => $user['fname'] . " " . $user['lname'], 'image' => $user['image'][0]['src']);
                }
            }

        } else {
           return array('name' => "NO" . " " . "NAME", 'image' => "https://lh3.googleusercontent.com/J6LxeBPR6auXQzy-xRyA0DLU_6dZDl9ktezmmN8fOP1PQkGwCs8uK_EzeFBNAg6-imJT0w=s152"); 
        }
        
    }
    
    public function getUserInfo($params) {
        $email = $params['email'];
        $userid = $params['userid'];
        $DB = $params['db'];
        $usersjson = $params['usersjson'];
        $item=explode("@", $email);
        $jsoninfo = $this->getUserNameFromJSON($item[0], $usersjson);
        
        //CHECK IF USER IS AN ADMIN
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        if ($userid == "me") {
            $arr = $selecteduser;
            $arr['image'] = $jsoninfo['image'];
                $arr['name'] = $jsoninfo['name'];
        } 
        else {
            if ($adminstatus == 1) {
                $request = $DB->fetch("SELECT * FROM welldays_users WHERE id = ?", $userid);
                $arr = $request;
                $arr['image'] = $jsoninfo['image'];
                $arr['name'] = $jsoninfo['name'];
            } 
            else {
                $arr = array('response' => "INVALID: YOU MUST BE AN ADMIN TO VIEW THIS USER.");
            }
        }
        echo json_encode(array('response' => $arr));
    }
    
    public function updateUserInfo($params) {
        $status = $params['action'];
        $email = $params['email'];
        $DB = $params['db'];
        
        //CHECK IF USER IS AN ADMIN
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);
        $adminstatus = $selecteduser["ADMIN"];
        
        //$updateadminstatus =$_POST['ADMIN'];
        $updateadminstatus = "1";
        if (empty($_POST['ADMIN'])) {
            $updateadminstatus = "0";
        }
        if ($adminstatus == 1) {
            $updatemydays = $DB->execute("UPDATE welldays_users SET supervisor = ?, days_available =?, ADMIN=? WHERE id = ?", array($_POST['supervisor'], $_POST['days_available'], $updateadminstatus, $params['userid']));
            $userobj = array('id' => $params['userid']);
            $arr = array('status' => "SUCCESS: USER UPDATED", 'user' => $userobj);
        } 
        else {
            $arr = array('admin_status' => $adminstatus);
        }
        echo json_encode(array('response' => $arr));
    }
    
    public function getCharities($params) {
        $DB = $params['db'];
        $request = $DB->fetchAll("SELECT * FROM welldays_charities");
        $charitylist=array();

        foreach ($request as $charity) {
            $nextcharityday = $DB->fetchAll("SELECT * FROM welldays_charitydays WHERE day_date > CURDATE() AND charity_id=? ORDER BY 'day_date' ASC LIMIT 1", array($charity["id"]));
            $nextday=null;
            if ($nextcharityday) {
                foreach ($nextcharityday as & $charityday) {
                    $nextday = array('id' => $charityday['id'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $charityday['day_date'], 'date_end' => $charityday['day_date_end'], 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                }
            }

            $prevcharityday = $DB->fetchAll("SELECT * FROM welldays_charitydays WHERE day_date < CURDATE() AND charity_id=? ORDER BY 'day_date' ASC LIMIT 1", array($charity["id"]));
            $prevday=null;
            if ($prevcharityday) {
                foreach ($prevcharityday as & $charityday) {
                    $prevday = array('id' => $charityday['id'], 'title' => $charityday['day_title'], 'description' => $charityday['day_desc'], 'date' => $charityday['day_date'], 'date_end' => $charityday['day_date_end'], 'charity id' => $charityday['charity_id'], 'occupancy' => $charityday['occupancy'], 'capacity' => $charityday['capacity'], 'latitude' => $charityday['lat'], 'longitude' => $charityday['long']);
                }
            }
       
            $charity["nextevent"]=$nextday;
            $charity["prevevent"]=$prevday;
            array_push($charitylist, $charity);
        }
        
       // $prevday = $this->getPrevCharityDay($params);
        //$arr = array('details' => $request,'next' => $nextday,'prev' => $prevday);
        
        echo json_encode(array('response' => $charitylist));
    }
    
    public function getUsers($params) {
        $DB = $params['db'];
        $email = $params['email'];
        
        //CHECK IF USER IS AN ADMIN
        $selecteduser = $DB->fetch("SELECT * FROM welldays_users WHERE user_email = ?", $email);

        $adminstatus = $selecteduser["ADMIN"];
        
        if ($adminstatus == 1) {
            $request = $DB->fetchAll("SELECT welldays_users.*,wp_users.display_name FROM welldays_users LEFT JOIN wp_users ON welldays_users.user_email = wp_users.user_email");
        
            $arr = array('users' => $request);
        } 
        else {
            $arr = array('status' => "ERROR: must be an ADMIN");
        }
        
        echo json_encode(array('response' => $arr));
    }
    
    public function getEvents($params) {
        $cal_service = $params['cal_service'];
        $calList = $cal_service->calendarList->listCalendarList()->items;
        $eventsarr = array();
        $dayevents = 0;
        $year = (string)$params['year'];
        $month = (string)$params['month'];
        $arr = array();
        $dayone = $year . '-' . $month . "-23";
        $start = $year . '-' . $month . '-01T00:00:01+00:00';
        $end = (string)date("Y-m-t", strtotime($dayone)) . 'T23:59:59+00:00';
        
        foreach ($calList as $calendar) {
            if (strrpos($calendar->id, "droga5.com")) {
                
                //cho $calendar->id, "<br /><br /> \n";
                
                // $start = date(DateTime::ATOM, mktime(0, 0, 0, date('m'), date('d'), date('Y')));
                // $end = date(DateTime::ATOM, mktime(23, 59, 59, date('m'), date('d'), date('Y')));
                
                //$optParams = array('timeMax' => $year.'-'.$month.'-31T23:59:59+00:00','timeMin' => $year.'-'.$month.'-01T00:00:01+00:00');
                $optParams = array("orderBy" => "startTime", "singleEvents" => true, "timeMin" => $start, "timeMax" => $end);
                $events = $cal_service->events->listEvents($calendar->id, $optParams);
                $dayevents = $events->items;
                
                //print_r($events->items);
                //echo count($dayevents), " EVENTS TODAY!<BR/>";
                $count = 0;
                foreach ($dayevents as $item) {
                    
                    //print_r($item->summary);
                    $count++;
                    $event_array = array('id' => $count, 'title' => $item->summary, 'description' => '$item->description', 'date' => $item->start->dateTime);
                    
                    array_push($eventsarr, $event_array);
                    
                    //echo "<br /><br />\n";
                    
                    
                }
                
                //echo "<br /> <br /> \n";
                
                
            }
        }
        
        //$arr = array('events' => $eventsarr);
        echo json_encode(array('response' => $eventsarr));
    }
    
    /*public function sendEmail() {
        
        $to = "mmosley@droga5.com";
        $subject = "My subject";
        $txt = "Hello world!";
        $headers = "From: webmaster@example.com" . "\r\n" . "CC: michaelsmosley@gmail.com";
        
        mail($to, $subject, $txt, $headers);
    }*/
    
    /*public function getEventsForDay($params) {
        $cal_service = $params['cal_service'];
        $calList = $cal_service->calendarList->listCalendarList()->items;
        $eventsarr = array();
        $dayevents = 0;
        $year = (string)$params['year'];
        $month = (string)$params['month'];
        $arr = array();
        $dayone = $year . '-' . $month . "-23";
        $start = $year . '-' . $month . '-01T00:00:01+00:00';
        $end = (string)date("Y-m-t", strtotime($dayone)) . 'T23:59:59+00:00';
        
        foreach ($calList as $calendar) {
            if (strrpos($calendar->id, "droga5.com")) {
                $optParams = array("orderBy" => "startTime", "singleEvents" => true, "timeMin" => $start, "timeMax" => $end);
                $events = $cal_service->events->listEvents($calendar->id, $optParams);
                $dayevents = $events->items;
                $count = 0;
                foreach ($dayevents as $item) {
                    $count++;
                    $event_array = array('id' => $count, 'title' => $item->summary, 'description' => '$item->description', 'date' => $item->start->dateTime);
                    array_push($eventsarr, $event_array);
                }
            }
        }
        echo json_encode(array('response' => $eventsarr));
    }*/
}
