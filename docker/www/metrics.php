<?php

header('content-type: text/plain; version=0.0.4; charset=utf-8; escaping=values');

$connect = new mysqli('pinba', 'root');

/*
DROP TABLE `report_by_script_name`;
CREATE TABLE `report_by_script_name` (
  `script` varchar(64) NOT NULL,
  `req_count` int(10) unsigned NOT NULL,
  `req_per_sec` float NOT NULL,
  `req_percent` float DEFAULT NULL,
  `req_time_total` float NOT NULL,
  `req_time_per_sec` float NOT NULL,
  `req_time_percent` float DEFAULT NULL,
  `ru_utime_total` float NOT NULL,
  `ru_utime_per_sec` float NOT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_stime_total` float NOT NULL,
  `ru_stime_per_sec` float NOT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `traffic_total` bigint(20) unsigned NOT NULL,
  `traffic_per_sec` float NOT NULL,
  `traffic_percent` float DEFAULT NULL,
  `memory_footprint` bigint(20) NOT NULL,
  `memory_per_sec` float NOT NULL,
  `memory_percent` float DEFAULT NULL
) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='v2/request/10/~script/no_percentiles/no_filters';
*/

$result = $connect->query(
    'select 
        script, 
        req_count, 
        req_per_sec, 
        req_time_total/req_count as time_avg, 
        ru_utime_total/req_count as ru_utime_avg,
        ru_stime_total/req_count as ru_stime_avg 
    from pinba.report_by_script_name'
);

foreach ($result as $row) {
    foreach(['req_per_sec', 'time_avg', 'ru_utime_avg', 'ru_stime_avg'] as $metric) {
        printf("pinba_%s{script_name=\"%s\"} %f\n", $metric, $row['script'], $row[$metric]);
    }
}
/*

DROP TABLE `report_script_span`;
CREATE TABLE `report_script_span` (
      `script` varchar(64) NOT NULL,
      `span` varchar(64) NOT NULL,
      `req_count` int(11) NOT NULL,
      `req_per_sec` float NOT NULL,
      `req_percent` float NOT NULL,
      `hit_count` int(11) NOT NULL,
      `hit_per_sec` float NOT NULL,
      `hit_percent` float NOT NULL,
      `time_total` float NOT NULL,
      `time_per_sec` float NOT NULL,
      `time_percent` float NOT NULL,
      `ru_utime_total` float NOT NULL,
      `ru_utime_per_sec` float NOT NULL,
      `ru_utime_percent` float NOT NULL,
      `ru_stime_total` float NOT NULL,
      `ru_stime_per_sec` float NOT NULL,
      `ru_stime_percent` float NOT NULL
    ) ENGINE=PINBA DEFAULT CHARSET=latin1 
    COMMENT='v2/timer/10/~script,@span/no_percentiles/no_filters';

    */
$result = $connect->query('
    select 
        script, 
        span, 
        req_count, 
        req_per_sec,
        hit_per_sec, 
        time_total/hit_count as time_avg, 
        ru_utime_total/hit_count as ru_utime_avg,
        ru_stime_total/hit_count as ru_stime_avg
    from pinba.report_script_span'
);

foreach ($result as $row) {
    foreach(['req_per_sec', 'hit_per_sec', 'time_avg', 'ru_utime_avg', 'ru_stime_avg'] as $metric) {
        printf("pinba_timer_%s{script_name=\"%s\",span=\"%s\"} %f\n", $metric, $row['script'], $row['span'], $row[$metric]);
    }
}

