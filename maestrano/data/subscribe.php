<?php

require_once '../init.php';
// require_once '../connec/init.php';

try {
  if(!Maestrano::param('connec.enabled')) { return false; }

  $client = new Maestrano_Connec_Client();

  $notification = json_decode(file_get_contents('php://input'), false);
  $entity_name = strtoupper(trim($notification->entity));
  $entity_id = $notification->id;

  error_log("Received notification = ". json_encode($notification));

  switch ($entity_name) {
    case "COMPANYS":
      $companyMapper = new CompanyMapper();
      $companyMapper->fetchConnecResource($entity_id);
      break;
    case "WORKLOCATIONS":
      $workLocationMapper = new WorkLocationMapper();
      $workLocationMapper->fetchConnecResource($entity_id);
      break;
    case "PAYSCHEDULES":
      $payScheduleMapper = new PayScheduleMapper();
      $payScheduleMapper->fetchConnecResource($entity_id);
      break;
    case "PAYITEMS":
      $payItemMapper = new PayItemMapper();
      $payItemMapper->fetchConnecResource($entity_id);
      break;
    case "EMPLOYEES":
      $employeeMapper = new EmployeeMapper();
      $employeeMapper->fetchConnecResource($entity_id);
      break;
    case "TIMEACTIVITIES":
      $timeActivityMapper = new TimeActivityMapper();
      $timeActivityMapper->fetchConnecResource($entity_id);
      break;
  }
} catch (Exception $e) {
  error_log("Caught exception in subscribe " . json_encode($e->getMessage()));
}

?>
