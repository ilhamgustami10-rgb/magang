<?php
try {
    $s = new COM("Schedule.Service");
    $s->Connect();
    $f = $s->GetFolder("\\");
    $t = $f->GetTask("Darsana Export");
    echo "Task found: " . $t->Name . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
