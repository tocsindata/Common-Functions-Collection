<?php


function human_time_diff($start_time, $end_time) {     

		$std_format = false ;
		$start_time = strtotime($start_time);
		$end_time = strtotime($end_time);
		  
		$total_time = $end_time - $start_time;
		$days       = floor($total_time /86400);        
		$hours      = floor($total_time /3600);     
		$minutes    = intval(($total_time/60) % 60);        
		$seconds    = intval($total_time % 60);     
		$results = "";
		if($std_format == false)
		{
		  if($days > 0) $results .= $days . (($days > 1)?" days ":" day ");     
		  if($hours > 0) $results .= $hours . (($hours > 1)?" hours ":" hour ");        
		  if($minutes > 0) $results .= $minutes . (($minutes > 1)?" minutes ":" minute ");
		  if($seconds > 0) $results .= $seconds . (($seconds > 1)?" seconds ":" second ");
		}
		else
		{
		  if($days > 0) $results = $days . (($days > 1)?" days ":" day ");
		  $results = sprintf("%s%02d:%02d:%02d",$results,$hours,$minutes,$seconds);
		}
return $results;
}
