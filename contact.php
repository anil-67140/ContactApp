<?php 
   include 'config/db_configu.php';
   $data = file_get_contents("php://input");
   $request = json_decode($data);
   $response = array();

   // Check if the JSON decode was successful
   if (is_null($request)) {
       $response['status'] = false;
       $response['responseCode'] = 107; // Invalid JSON
       $response['message'] = "Invalid JSON input";
       echo json_encode($response);
       exit();
   }

   $isValidRequest = false;

   if (isset($request->action)) {
       
       // ADD_CONTACT
       if ($request->action == 'ADD_CONTACT') {
           $isValidRequest = true;
           $Id = $request->Id;
           $Name = $request->Name;
           $MobileNo = $request->MobileNo;
           $Email = $request->Email;

           $query = "INSERT INTO Contact(Id, Name, MobileNo, Email) VALUES (?, ?, ?, ?)";
           $stmt = $connection->prepare($query);
           $stmt->bind_param("ssss", $Id, $Name, $MobileNo, $Email);
           $result = $stmt->execute();

           if ($result) {
               $response['Id'] = $stmt->insert_id;
               $response['status'] = true;
               $response['responseCode'] = 0; // success
               $response['message'] = "Contact inserted successfully";
           } else {
               $response['status'] = false;
               $response['responseCode'] = 103; // Contact insertion failed
               $response['message'] = "Contact insertion failed";
           }
       }
       
       // GET_CONTACT
       if ($request->action == 'GET_CONTACT') {
           $isValidRequest = true;
           $Id = $request->Id;

           $query = "SELECT Id, Name, MobileNo, Email FROM Contact WHERE Id = ?";
           $stmt = $connection->prepare($query);
           $stmt->bind_param("s", $Id);
           $stmt->execute();
           $result = $stmt->get_result();

           if ($result && $result->num_rows > 0) {
               $Contacts = array();
               while ($row = $result->fetch_assoc()) {
                   $Contacts[] = $row;
               }
               $response['status'] = true;
               $response['responseCode'] = 0; // Contacts are available
               $response['message'] = "Contacts are available";
               $response['Contacts'] = $Contacts;
           } else {
               $response['status'] = false;
               $response['responseCode'] = 104; // Contacts are not available
               $response['message'] = "Contacts are not available";
           }
       }

       // UPDATE_CONTACT
       if ($request->action == 'UPDATE_CONTACT') {
           $isValidRequest = true;

           $Id = $request->Id;
           $Name = $request->Name;
           $MobileNo = $request->MobileNo;
           $Email = $request->Email;

           $query = "UPDATE Contact SET Name = ?, MobileNo = ?, Email = ? WHERE Id = ?";
           $stmt = $connection->prepare($query);
           $stmt->bind_param("ssss", $Name, $MobileNo, $Email, $Id);
           $result = $stmt->execute();

           if ($result) {
               $response['Id'] = $Id;
               $response['status'] = true;
               $response['responseCode'] = 0; // success
               $response['message'] = "Contact updated successfully";
           } else {
               $response['status'] = false;
               $response['responseCode'] = 105; // Contact update failed
               $response['message'] = "Contact update failed";
           }
       } 

       // DELETE_CONTACT
       if ($request->action == 'DELETE_CONTACT') {
           $isValidRequest = true;

           $Id = $request->Id;

           $query = "DELETE FROM Contact WHERE Id = ?";
           $stmt = $connection->prepare($query);
           $stmt->bind_param("s", $Id);
           $result = $stmt->execute();

           if ($result) {
               $response['Id'] = $Id;
               $response['status'] = true;
               $response['responseCode'] = 0; // success
               $response['message'] = "Contact deleted successfully";
           } else {
               $response['status'] = false;
               $response['responseCode'] = 106; // Contact deletion failed
               $response['message'] = "Contact deletion failed";
           }
       }

       if (!$isValidRequest) {
           $response['status'] = false;
           $response['responseCode'] = 101; // invalid request action 
           $response['message'] = "Invalid request action";
       }

   } else {
       $response['status'] = false;
       $response['responseCode'] = 100; // request action is not defined
       $response['message'] = "Request action is not defined";
   }

   echo json_encode($response);
?>
