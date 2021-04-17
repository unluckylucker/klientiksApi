<?php
require __DIR__.'/class.php';

$crm = new CrmUserClass;
$result = $crm->addRandomUsersToCrm(
    50, [
        'start'=>'01.01.1970',
        'end'=>'31.12.2000'
    ]
);
echo "<pre>";
print_r($result);
echo "</pre>";