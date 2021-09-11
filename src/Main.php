<?php

include 'class/User.php';
include 'class/Trip.php';
include 'class/SessionSecuirty.php';

include 'database/UsersDB.php';
include 'database/TripsDB.php';
include 'database/CurrentBookingsDB.php';
include 'database/PastBookingsDB.php';

include 'system/ClearCLI.php';

$sessionSecurity = new SessionSecurity();

$usersDB = new UsersDB();
$tripsDB = new TripsDB();
$currentBookingsDB = new CurrentBookingsDB();
$pastBookingsDB = new PastBookingsDB();

$system = new ClearCLI();

$isTrue = true;
while ($isTrue) {
  $system->clearZeroWaiting();

  echo "\n\n------------------------Options------------------------";
  echo "\n1 - Create account";
  echo "\n2 - Login";
  echo "\nex - Exit\n";

  $option = readline();
  switch ($option) {
    case 'ex':   // exit
      $isTrue = false;
      echo "\nWe hope that you enjoy! 😎\n";
      $system->clearThreeWaiting();
      break;

    case '1':   // create account
      $isTrueCreateAccount = true;
      while ($isTrueCreateAccount) {
        $system->clearZeroWaiting();

        echo "\n\n------------------------Options------------------------";
        echo "\n1 - Create account";
        echo "\n0 - Return\n";
  
        $optionCreateAccount = readline();
        switch ($optionCreateAccount) {
          case '0':   // create account -> return
            $isTrueCreateAccount = false;
            break;
  
          case '1':   // create account -> create account
            $system->clearZeroWaiting();

            echo "\nCreation of an account\n";
            $username = readline('Insert your username: ');

            echo "\nRequirements of password: ";
            echo "\n- Minimum 8 characters in length";
            echo "\n- Contains 3 of 4 of the following items:";
            echo "\n  - Uppercase Letters";
            echo "\n  - Lowercase Letters";
            echo "\n  - Numbers";
            echo "\n  - Symbols\n\n";

            echo 'Insert your password: ';
            $password = Seld\CliPrompt\CliPrompt::hiddenPrompt();
            
            if (!isStrongPassword($password)) {
              echo "\nPassword don't match the requirements 😞";
              $system->sleepThree();
              break;
            }

            echo "\nStrong Password 💪\n\n";

            echo 'Confirm your password: ';
            $passwordConfirmation = Seld\CliPrompt\CliPrompt::hiddenPrompt();
            
            if ($password == $passwordConfirmation) {
              try {
                $user = new User($username, $password);
      
                $errorDB = $usersDB->insertUser($user->getUsername(), $user->generateHashPassword()); // insert to db
      
                if ($errorDB) {
                  echo "\nError ocurred\n";
                  echo $errorDB;
                  $system->sleepThree();
                  break;
                }
                
                $isTrueCreateAccount = false;
                echo "Account created successfully 😎";
                $system->sleepThree();
      
              } catch (PDOException $e) {
                echo $e->getMessage();
                $system->sleepThree();
              }
      
            } else{
              echo "\nPassword and Confirmation Password don't match\n";
              $system->sleepThree();
            }
            break;
            
          default:
            echo "\nYou choose $optionCreateAccount.\nOption not available at the moment.";
            $system->sleepThree();
        }
      }
      break;

    case "2":   // login
      $isTrueLogin = true;
      while ($isTrueLogin) {
        $system->clearZeroWaiting();

        echo "\n\n------------------------Options------------------------";
        echo "\n1 - Login";
        echo "\n0 - Return\n";
  
        $optionLogin = readline();
        switch ($optionLogin) {
          case '0':   // login -> return
            $isTrueLogin = false;
            break;
  
          case '1':   // login -> login
            $system->clearZeroWaiting();

            echo "\nLogin\n";
            $username = readline('Insert your username: ');
            echo 'Insert your password: ';
            $password = Seld\CliPrompt\CliPrompt::hiddenPrompt();
  
            try {
              $user = new User($username, $password);
    
              $auth = $usersDB->authenticationUser($user->getUsername(), $user->getPassword());   // authenticate user
  
              if (!$auth) {
                echo "\nError ocurred.";
                echo "\nPlease try again or create an account.";
                $system->sleepThree();
                break;
              }
    
              $isTrueLogin = false;
              echo "\nLogged in...";

              $_SESSION['U'] = $sessionSecurity->encryptRSA($user->getUsername());   // session username encrypted
              $_SESSION['P'] = $sessionSecurity->encryptRSA($user->getPassword());   // session password encrypted

              $system->sleepOne();

              $isTrueAccount = true;
              while ($isTrueAccount) {
                $system->clearZeroWaiting();
                
                echo "\n\n------------------------Options------------------------";
                echo "\n1 - Account Information";
                echo "\n2 - Booking a trip";
                echo "\n3 - Show your booking information";
                echo "\n0 - Log out";
                echo "\nex - Exit\n";
  
                $optionAccount = readline();
                switch ($optionAccount) {
                  case '0':   // login -> account -> log out
                    unset($_SESSION['U']);
                    unset($_SESSION['P']);

                    echo "\nLogging out...";
                    $isTrueLogin = false;
                    $isTrueAccount = false;
                    $system->sleepThree();
                    break;

                  case 'ex':  // login -> account -> exit
                    unset($_SESSION['U']);
                    unset($_SESSION['P']);

                    $isTrue               = false;
                    $isTrueAccount        = false;
                    $isTrueLogin          = false;

                    echo "\nLogging out...";
                    $system->sleepOne();
                    echo "\nWe hope that you enjoy! 😎\n";
                    $system->clearThreeWaiting();
                    break;

                  case '1':   // login -> account -> account information
                    $system->clearZeroWaiting();

                    echo "\n\n------------------------Account information------------------------";

                    $userInfo = $usersDB->getInformationUser($sessionSecurity->decryptRSA($_SESSION['U']));

                    echo "\n\nUsername: " . $sessionSecurity->decryptRSA($_SESSION['U']);
                    echo "\nPassword: " . str_repeat('*', strlen($sessionSecurity->decryptRSA($_SESSION['P'])) + 3);
                    echo "\nDate of creation: " . $userInfo['DateAccountCreation'];
                    echo "\nDate of last password modification: " . $userInfo['DatePasswordModification'];

                    echo "\n\n------------------------Options------------------------";
                    echo "\n1 - Change password\n";
                    echo "\nClick other button to return\n";

                    $optionAccountInfo = readline();
                    if ($optionAccountInfo == '1') {
                      echo 'Insert your password: ';
                      $password = Seld\CliPrompt\CliPrompt::hiddenPrompt();

                      try {
                        $auth = $usersDB->authenticationUser($sessionSecurity->decryptRSA($_SESSION['U']),
                                        $password, $sessionSecurity);   // authenticate user
          
                        if (!$auth) {
                          echo "\nError ocurred";
                          $system->sleepThree();
                          break;
                        }

                        echo "\nRequirements of password: ";
                        echo "\n- Minimum 8 characters in length";
                        echo "\n- Contains 3 of 4 of the following items:";
                        echo "\n  - Uppercase Letters";
                        echo "\n  - Lowercase Letters";
                        echo "\n  - Numbers";
                        echo "\n  - Symbols\n\n";

                        echo 'Insert new password: ';
                        $newPassword = Seld\CliPrompt\CliPrompt::hiddenPrompt();

                        if (!isStrongPassword($newPassword)) {
                          echo "\nPassword don't match the requirements 😞";
                          $system->sleepThree();
                          break;
                        }
            
                        echo "\nStrong Password 💪\n\n";

                        echo 'Confirm new password: ';
                        $newPasswordConfirmation = Seld\CliPrompt\CliPrompt::hiddenPrompt();

                        if ($newPassword == $newPasswordConfirmation) {
                          $newUser = new User($sessionSecurity->decryptRSA($_SESSION['U']), $newPassword);
                          $usersDB->changePassword($newUser->getUsername(), $newUser->generateHashPassword());

                          // check if occur any error

                          echo "\nPassword changed successfully";
                          $_SESSION['P'] = $sessionSecurity->encryptRSA($newUser->getPassword());
                          $system->sleepThree();
                        } else {
                          echo "\nPasswords inserted don't match";
                          $system->sleepThree();
                          break;
                        }
                      } catch (PDOException $e) {
                        echo $e->getMessage();
                        $system->sleepThree();
                      }
                    }
                    break;

                  case '2':   // login -> account -> booking
                    $isTrueBookTrip = true;
                    while ($isTrueBookTrip) {
                      $system->clearZeroWaiting();

                      echo "\n\n------------------------Options------------------------";
                      echo "\n1 - Booking a trip";
                      echo "\n0 - Return\n";
  
                      $optionTrip = readline();
                      switch ($optionTrip) {
                        case '0':   // login -> account -> booking -> return
                          $isTrueBookTrip = false;
                          break;
                        
                        case '1':   // login -> account -> booking
                          $system->clearZeroWaiting();

                          echo "\n\n------------------------Choose your trip------------------------";
                          
                          $trip = new Trip('', '', '');
  
                          echo "\nFrom: ";
                          $from = readline();
  
                          $errObj = $trip->standardString($from);   // change variable name errObj
                          if ($errObj->getError()) {
                            echo $errObj->getError();
                            $system->sleepThree();
                            break;
                          }
                          $trip->setFrom($errObj->getLocation());
  
                          echo "\nTo: ";
                          $to = readline();
  
                          $errObj = $trip->standardString($to);   // change variable name errObj
                          if ($errObj->getError()) {
                            echo $errObj->getError();
                            $system->sleepThree();
                            break;
                          }
                          $trip->setTo($errObj->getLocation());
  
                          echo "\nDate (format day(number)/month(number)/year(number) || example => 02/09/2010): ";
                          $date = readline();
  
                          $errObj = $trip->dateToISO($date);   // change variable name errObj
                          if ($errObj->getError()) {
                            echo $errObj->getError();
                            $system->sleepThree();
                            break;
                          }
                          $trip->setDate($errObj->getDate());
  
                          // get trips available
  
                          $tripsReturned = $tripsDB->getTrips($trip);

                          // check if occur any error

                          $system->clearThreeWaiting();
  
                          echo "\nTrips available\n";
                          foreach ($tripsReturned as $eachTrip) {

                            $driver = $tripsDB->getDriver($eachTrip['Driver']);
                            $bus = $tripsDB->getBus($eachTrip['Bus']);
                            // check if occur any error
                            
                            echo "\n\nID: " . $eachTrip['ID'];
                            echo "\nFrom: " . $eachTrip['From'];
                            echo "\nTo: " . $eachTrip['To'];
                            echo "\nBus: " . $bus['Name'];
                            echo "\nDriver: " . $driver['Name'];
                            echo "\nPassengers: " . $eachTrip['Passengers'];
                            echo "\nDate: " . $eachTrip['Date'];
                            echo "\nTime: " . $eachTrip['Time'];
                          }
  
                          echo "\n\nDo you want to book one this trips?";
                          echo "\nInsert (y) if you want and any other to cancel: ";
                          $continueResponse = readline();
  
                          if ($continueResponse != 'y') {
                            $isTrueBookTrip = false;
                            break;
                          }
  
                          // book the trip
  
                          echo "\n\n------------------------Choose your trip------------------------";
                          echo "\nInsert the trip ID that you want to book: ";
                          $bookID = readline();
  
                          foreach ($tripsReturned as $eachTrip) {
                            if ($bookID == $eachTrip['ID']) {

                              $bus = $tripsDB->getBus($eachTrip['Bus']);
                              
                              // make the book
                              if ($eachTrip['Passengers'] < $bus['MaxPassengers']) {
                                $currentBookingsDB->makeBook($eachTrip['ID'],
                                                        $sessionSecurity->decryptRSA($_SESSION['U']));
                                
                                // check if occur any error
                                echo "\n\nBook done successfully! 😎";
                                $system->sleepThree();
                              }
                            }
                          }
                          break;
  
                        default:
                          echo "You choose $optionTrip.\nOption not available at the moment.";
                          $system->sleepThree();
                      }
                    }
                    break;

                  case '3':   // login -> account -> booking information
                    echo "\n\n------------------------Booking Information------------------------";

                    echo "\n\n------------------------Current Bookings------------------------";

                    $returnedBookings = $currentBookingsDB->getBookingByUser($sessionSecurity->decryptRSA($_SESSION['U']));

                    foreach ($returnedBookings as $eachBooking) {
                      echo "\n\nID: " . $eachBooking['ID'];
                      echo "\nTrip: " . $eachBooking['Trip'];
                      echo "\nDateTimeBooking: " . $eachBooking['DateTimeBooking'];
                    }

                    echo "\n\n------------------------Past Bookings------------------------";

                    $returnedBookings = $pastBookingsDB->getBookingByUser($sessionSecurity->decryptRSA($_SESSION['U']));

                    foreach ($returnedBookings as $eachBooking) {
                      echo "\n\nID: " . $eachBooking['ID'];
                      echo "\nTrip: " . $eachBooking['Trip'];
                      echo "\nDateTimeBooking: " . $eachBooking['DateTimeBooking'];
                    }
                      
                    echo "\n\n------------------------Options------------------------";
                    echo "\nClick any button to return\n";

                    readline();
                    break;

                  default:
                    echo "You choose $optionAccount.\nOption not available at the moment.";
                    $system->sleepThree();
                }
              }
            } catch (PDOException $e) {
              echo $e->getMessage();
              $system->sleepThree();
            }
            break;
  
          default:
            echo "You choose $optionCreateAccount.\nOption not available at the moment.";
            $system->sleepThree();
        }
      }
      break;

    default:
      echo "You choose $option.\nOption not available at the moment.";
      $system->sleepThree();
  }
}

function isStrongPassword ($password) {
  if (strlen($password) < 8) {
    return false;
  }

  $countRequirements = 0;

  // check lowercase characters
  if (mb_strtoupper($password, "UTF-8") != $password) {
    $countRequirements++;
  }

  // check uppercase characters
  if (mb_strtolower($password, "UTF-8") != $password) {
    $countRequirements++;
  }

  // check numbers characters
  if (preg_match('~[0-9]+~', $password)) {
    $countRequirements++;
  }

  // check symbols characters
  if (!ctype_alnum($password)) {
    $countRequirements++;
  }

  return $countRequirements >= 3 ? true : false;    // verify line
}

?>