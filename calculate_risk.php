<?php

require_once "db.php";

function calculateRisk($student_id,$conn)
{

    $stmt = $conn->prepare("
        SELECT mood
        FROM mood_logs
        WHERE student_id=?
        ORDER BY id DESC
        LIMIT 7
    ");

    $stmt->bind_param("i",$student_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $score=0;

    while($row=$result->fetch_assoc())
    {

        switch(strtolower($row["mood"]))
        {

            case "happy":
                $score+=0;
            break;

            case "calm":
                $score+=1;
            break;

            case "excited":
                $score+=0;
            break;

            case "sad":
                $score+=3;
            break;

            case "stressed":
                $score+=4;
            break;

            case "angry":
                $score+=5;
            break;

        }

    }

    if($score>=20)
    {
        $level="High";
    }
    elseif($score>=10)
    {
        $level="Medium";
    }
    else
    {
        $level="Low";
    }

    $check=$conn->prepare("SELECT id FROM wellness_risk WHERE student_id=?");
    $check->bind_param("i",$student_id);
    $check->execute();

    if($check->get_result()->num_rows>0)
    {

        $update=$conn->prepare("
            UPDATE wellness_risk
            SET risk_score=?,
                risk_level=?
            WHERE student_id=?
        ");

        $update->bind_param("isi",$score,$level,$student_id);
        $update->execute();

    }
    else
    {

        $insert=$conn->prepare("
            INSERT INTO wellness_risk
            (student_id,risk_score,risk_level)
            VALUES(?,?,?)
        ");

        $insert->bind_param("iis",$student_id,$score,$level);
        $insert->execute();

    }

}
?>