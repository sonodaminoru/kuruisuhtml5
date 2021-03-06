<?php
//require_once './pdo_connect.php';
$rows = array();
$nodeinfo = array();
define("INFI",1000000000);
$sql = "select * from Link";
$stmt = $dbh->query($sql);
foreach ($stmt as $row){
	$nodeinfo[(string)$row['StartNode']]=array();
	array_push($rows,array((string)$row['StartNode'],(string)$row['EndNode']=>$row['high_dump']));
}
for($i=0;$i<count($rows);$i++){
	$nodeinfo[$rows[$i][0]] = $nodeinfo[$rows[$i][0]] + array_slice($rows[$i], 1, 1, true);
}

//$n = 6; //データベーステーブルNodeの行数より取得
$start = 1; //今後,入力された緯度経度値より求めるとこまでできたらいい
$end = 6;

function dijkstra($nodeinfo,$start,$end){
	$cost = array(); //スタート地点から各ノードへの最小コスト
	$route = array(); //スタートから各ノードまでの最小コスト経路
	$used = array(); //探索が終わったノード

	foreach($nodeinfo as $node => $info){
		$cost[$node] = INFI;
		$route[$node] = array();
	}
	$cost[$start] = 0;

	while(true){
		foreach($nodeinfo[$start] as $next => $edgecost){
			if($cost[$next] > $edgecost + $cost[$start]){
				$cost[$next] = $edgecost + $cost[$start];
        			$route[$next] = $route[$start];
				$route[$next][] = $start;
			}
		}
		$used[] = $start;
		$start = next_start($used,$cost);	
		if($start === $end){
			break;
		}
	}
        $route[$end][] = $end;
	
	//print_r($cost[$end]);
	//print_r($route[$end]);
	return $route[$end];
}

function  next_start($used,$cost){
	$tmp=array();
	$min = INFI;
	$prov;

        foreach($cost as $key => $value){
                if(!in_array($key,$used) && $value !== INFI){
                        $tmp[$key] = $value;
                }
        }
	foreach($tmp as $key => $value){
		if($min > $value){
			$prov = $key;
			$min = $value;
		}
	}
	return $prov;		
}

$waypoints = dijkstra($nodeinfo,$start,$end);

//最小コストルートを実際の緯度経度情報に置き換える
for($i=0;$i<count($waypoints);$i++){
	$sql = "select X(Latlon),Y(Latlon) from Node where NodeNo = {$waypoints[$i]}";
	$stmt = $dbh->query($sql);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$waypoints[$i] = array($result['X(Latlon)'],$result['Y(Latlon)']);
}

$waypoints = json_safe_encode($waypoints);
//print_r($waypoints);
