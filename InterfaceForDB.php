$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
    array(
        'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["path"],'/').';host='.$dbopts["host"] . ';port=' . $dbopts["port"],
        'pdo.username' => $dbopts["user"],
        'pdo.password' => $dbopts["pass"]
    )
);

$pdo = $app['pdo'];


$recvQRString = $_POST["id"];
$recvQRString = pg_escape_string($dbconn, $recvQRString);
$isSpeakerPresent = false;
$isTeamPresent = false;
$speaker_name = "";
$speaker_team_name = "";
$isSpeakerAdj = false;

$qryMembersWithID = "SELECT * FROM check_in_members JOIN check_in_teams ON check_in_members.fk_teamid = check_in_teams.teamid WHERE check_in_members.speaker_identifier='$recvQRString'");
$rstMembersWithID = $pdo->query($qryMembersWithID);

while ($row = pg_fetch_assoc($rstMembersWithID) {
    $speaker_name = $row['check_in_members.speaker_name'];
    if ($row['check_in_members.adj'] == "1") {
        $isSpeakerAdj = true;
    }
    if ($row['check_in_members.present'] == "1") {
        $isSpeakerPresent = true;
    }
    if ($row['check_in_teams.present'] == "1") {
        $isTeamPresent = true;
    }

    if (!$isSpeakerAdj) {
        $speaker_team_name = $row['check_in_teams.team_name'];
    } else {
        $speaker_team_name = "Adjudicator";
    }
}

if (!$isSpeakerPresent) {
    $updateMembersPresentStatus = "UPDATE check_in_members SET present=1 WHERE entryid='$tbdefined'";
    $pdo->query($updateMembersPresentStatus);
}

if (!$isTeamPresent) {
    $updateTeamPresentStatus = "UPDATE check_in_teams SET present=1 WHERE teamid='$tbdefined'";
    $pdo->query($updateTeamPresentStatus);
}

echo "$speaker_name,$speaker_team_name,$isSpeakerPresent,$isTeamPresent";