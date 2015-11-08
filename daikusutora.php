<?php
require_once './pdo_connect.php';
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

$n = 6; //データベーステーブルNodeの行数より取得
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
				$route = route_reset($route,$start,$next);
				$route[$next][] = $start;
			}
		}
		$used[] = $start;
		$start = next_start($used,$cost);	
		if($start === $end){
			break;
		}
	}
	
	//print_r($route);
	print_r($cost);

	$route[$end][] = $end;

	print_r($route[$end]);
	//return $route[$end];
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

function route_reset($route,$start,$next){
	$route[$next] = array();
	$route[$next] = $route[$start];
	return $route;
}

dijkstra($nodeinfo,$start,$end);
