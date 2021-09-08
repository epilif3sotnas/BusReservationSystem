<?php

include 'class/User.php';
include 'class/Trip.php';

include 'database/UsersDB.php';
include 'database/TripsDB.php';
include 'database/CurrentBookingsDB.php';
include 'database/PastBookingsDB.php';

include 'system/ClearCLI.php';

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
      $system->clearFiveWaiting();
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
            $password = readline('Insert your password: ');                 // improvement -> hide input
            // improvement -> password quality
            $passwordConfirmation = readline('Confirm your password: ');    // improvement -> hide input
            
            if ($password == $passwordConfirmation) {
              try {
                $user = new User($username, $password);
      
                $errorDB = $usersDB->insertUser($user->getUsername(), $user->generateHashPassword()); // insert to db
      
                if ($errorDB) {
                  echo "\nError ocurred\n";
                  echo $errorDB;
                  $system->sleepFive();
                  break;
                }
                
                $isTrueCreateAccount = false;
                echo "Account created successfully 😎";
      
              } catch (PDOException $e) {
                echo $e->getMessage();
                $system->sleepFive();
              }
      
            } else{
              echo "\nPassword and Confirmation Password don't match\n";
              $system->sleepFive();
            }
            break;
            
          default:
            echo "\nYou choose $optionCreateAccount.\nOption not available at the moment.";
            $system->sleepFive();
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
            $password = readline('Insert your password: ');   // improvement -> hide input
  
            try {
              $user = new User($username, $password);
    
              $auth = $usersDB->authenticationUser($user->getUsername(), $user->getPassword());   // authenticate user
  
              if (!$auth) {
                echo "\nError ocurred.";
                echo "\nPlease try again or create an account.";
                $system->sleepFive();
                break;
              }
    
              $isTrueLogin = false;
              echo "\nLogged in...";

              $_SESSION["Username"] = $user->getUsername();   // improvement -> encrypt
              $_SESSION["Password"] = $user->getPassword();   // improvement -> encrypt

              $isTrueAccount = true;
              while ($isTrueAccount) {
                $system->clearFiveWaiting();
                
                echo "\n\n------------------------Options------------------------";
                echo "\n1 - Account Information";
                echo "\n2 - Booking a trip";
                echo "\n3 - Show your booking information";
                echo "\n0 - Log out";
                echo "\nex - Exit\n";
  
                $optionAccount = readline();
                switch ($optionAccount) {
                  case '0':   // login -> account -> log out
                    unset($_SESSION['Username']);
                    unset($_SESSION['Password']);

                    echo "\nLogging out...";
                    $isTrueLogin = false;
                    $isTrueAccount = false;
                    $system->sleepFive();
                    break;

                  case 'ex':  // login -> account -> exit
                    unset($_SESSION['Username']);
                    unset($_SESSION['Password']);

                    $isTrue               = false;
                    $isTrueAccount        = false;
                    $isTrueLogin          = false;

                    echo "\nLogging out...";
                    $system->sleepThree();
                    echo "\nWe hope that you enjoy! 😎\n";
                    $system->clearThreeWaiting();
                    break;

                  case '1':   // login -> account -> account information
                    $system->clearZeroWaiting();

                    echo "\n\n------------------------Account information------------------------";

                    $userInfo = $usersDB->getInformationUser($_SESSION['Username']);

                    echo "\n\nUsername: " . $_SESSION['Username'];
                    echo "\nPassword: " . str_repeat('*', strlen($_SESSION['Password']));
                    echo "\nDate of creation: " . $userInfo['DateAccountCreation'];
                    echo "\nDate of last password modification: " . $userInfo['DatePasswordModification'];

                    echo "\n\n------------------------Options------------------------";
                    echo "\n1 - Change password\n";
                    echo "\nClick other button to return\n";

                    $optionAccountInfo = readline();
                    if ($optionAccountInfo == '1') {
                      echo "\n\nInsert your password: ";
                      $password = readline();

                      try {
                        $auth = $usersDB->authenticationUser($_SESSION['Username'], $password);   // authenticate user
          
                        if (!$auth) {
                          echo "\nError ocurred";
                          $system->sleepFive();
                          break;
                        }

                        echo "\n\nInsert new password: ";
                        $newPassword = readline();

                        echo "\nConfirm new password: ";
                        $newPasswordConfirmation = readline();

                        if ($newPassword == $newPasswordConfirmation) {
                          $newUser = new User($_SESSION['Username'], $newPassword);
                          $usersDB->changePassword($newUser->getUsername(), $newUser->generateHashPassword());

                          echo "\nPassword changed successfully";
                          $system->sleepFive();
                        } else {
                          echo "\nPasswords inserted don't match";
                          $system->sleepFive();
                          break;
                        }
                      } catch (PDOException $e) {
                        echo $e->getMessage();
                        $system->sleepFive();
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
  
                          $errObj = $trip->standardString($from);
                          if ($errObj->getError()) {
                            echo $errObj->getError();
                            $system->sleepFive();
                            break;
                          }
                          $trip->setFrom($errObj->getLocation());
  
                          echo "\nTo: ";
                          $to = readline();
  
                          $errObj = $trip->standardString($to);
                          if ($errObj->getError()) {
                            echo $errObj->getError();
                            $system->sleepFive();
                            break;
                          }
                          $trip->setTo($errObj->getLocation());
  
                          echo "\nDate (format day(number)/month(number)/year(number) || example => 02/09/2010): ";
                          $date = readline();
  
                          $errObj = $trip->dateToISO($date);
                          if ($errObj->getError()) {
                            echo $errObj->getError();
                            $system->sleepFive();
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
                                $currentBookingsDB->makeBook($eachTrip['ID'], $_SESSION["Username"]);
                                
                                // check if occur any error
                                echo "\n\nBook done successfully! 😎";
                                $system->sleepFive();
                              }
                            }
                          }
                          break;
  
                        default:
                          echo "You choose $optionTrip.\nOption not available at the moment.";
                          $system->sleepFive();
                      }
                    }
                    break;

                  case '3':   // login -> account -> booking information
                    echo "\n\n------------------------Booking Information------------------------";

                    echo "\n\n------------------------Current Bookings------------------------";

                    $returnedBookings = $currentBookingsDB->getBookingByUser($_SESSION['Username']);

                    foreach ($returnedBookings as $eachBooking) {
                      echo "\n\nID: " . $eachBooking['ID'];
                      echo "\nTrip: " . $eachBooking['Trip'];
                      echo "\nDateTimeBooking: " . $eachBooking['DateTimeBooking'];
                    }

                    echo "\n\n------------------------Past Bookings------------------------";

                    $returnedBookings = $pastBookingsDB->getBookingByUser($_SESSION['Username']);

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
                    $system->sleepFive();
                }
              }
            } catch (PDOException $e) {
              echo $e->getMessage();
              $system->sleepFive();
            }
            break;
  
          default:
            echo "You choose $optionCreateAccount.\nOption not available at the moment.";
            $system->sleepFive();
        }
      }
      break;

    default:
      echo "You choose $option.\nOption not available at the moment.";
      $system->sleepFive();
  }
}

?>