<?
$lines =preg_split(":[\r\n]+:", file_get_contents("course_list.txt"));
$course = [];
$courses = [];
$localCount = 0;
$months = [
    "jan"=>1,
    "feb"=>2,
    "mar"=>3,
    "apr"=>4,
    "may"=>5,
    //"jan"=>1,
];

$tz_offsets = [
    "Eastern" => 0,
    "Central" => 1,
    "Mountain" => 2,
    "Pacific" => 3,
];
foreach ($lines as $line){
    if($line == "REGISTER NOW"){
        $courses[]= $course;
        $course = [
            "is_late"=>0,
        ];
        $localCount = 0;
        continue;
    }
    $localCount++;
    if($localCount == 1){
        preg_match(":(.{3})\\s+(\\d+)\\s+(.*)\\s+$.*:",$line,$info);
        preg_match(":(.{3})\\s+(\\d+)\\s+([a-zA-Z0-9 '\\:&]*).*:",$line,$info);
        $course["month"]=$info[1];
        $course["date"]=$info[2];
        $course["desc"]=trim($info[3]);
        @$course["ts"]=mktime(0,0,0,$months[strtolower($info[1])],$info[2]+0,2017);
        @$course["dow"]=date("D",$course["ts"]);
        if($course["dow"] == "Sat" || $course["dow"]=="Sun"){
            $course["is_late"] = 1;
        }
        $words = preg_split(":\\s+:",$line);
        $word_len = count($words);
        $course["tz"] = $words[$word_len - 3];
        //@$course["ts"]=strtotime("2017 {$info[1]} {$info[2]}");
    }
    if(preg_match(":(\\d+\\.?\\d*)\s+\\D+\s+(\\d*)\\:(.*)\s+-\s+(\\d*)\\:(.*):",$line,$info)){
        $course["credit"]=$info[1] + 0;
        $tz_offset = $tz_offsets[$course["tz"]];
        $info[2]+=$tz_offset;
        $info[4]+=$tz_offset;
        if(preg_match(":.*PM.*:",$info[3]) && ($info[2] + 0 ) < 12){
            $info[2] +=12;
        }
        if(preg_match(":.*PM.*:",$info[5]) && ($info[4] + 0 ) < 12){
            $info[4] +=12;
        }
        if($info[2] >= 16){
            $course["is_late"]=1;
        }
        $course["start_time"]=$info[2].":".substr($info[3],0,2);
        $course["end_time"]=$info[4].":".substr($info[5],0,2);
    }
}
$totalTime = 0;
foreach($courses as $course){
    if($course["is_late"]){
        $totalTime += $course["credit"];
        printf("%s %s %2s : %5s - %5s %2.1f %s\n",
            $course["dow"],
            $course["month"],
            $course["date"],
            $course["start_time"],
            $course["end_time"], 
            $course["credit"],
            $course["desc"]);
        //print_r($course);
    }
} 
printf("Total Time: %3.1f\n",$totalTime);
//print_r($courses[1]);
//echo count($lines);
?>
