<?php

class DB
{
	var $db_host, $db_name, $db_conn, $count;
	var $debug = 0;
	var $error_pass = 0;

	function connect($db_host,$db_user,$db_pass){
		$this->db_conn = @mysqli_connect($db_host, $db_user, $db_pass);
		if (!$this->db_conn){
			$err['msg'] = 'DB connection error..';
			$this->error($err);
		}
		$this->db_host = $db_host;
	}

	function select($db_name){
		@$ret = mysqli_select_db($this->db_conn,$db_name);
		if (!$ret){
			$err['msg'] = 'DB selection error..';
			$this->error($err);
		} else $this->db_name = $db_name;
	}

	function query($query){
		$time[] = $this->microtime_float();

		$debug = @debug_backtrace();
		krsort($debug);
		foreach ($debug as $v) $debuginf[] = $v['file']." (line:$v[line])";
		$debuginf = implode("<br>",$debuginf);

		if ($this->debug && !preg_match("/^select/",trim($query))){
			debug($query);
			return;
		}

		$res = mysqli_query($this->db_conn,$query);
		if (!$res){
			$err['query']	= htmlspecialchars($query);
			$err['file']	= $debuginf;
			$this->error($err);
		} else {
			if (preg_match("/^select/",trim($query))) $this->count = $this->count_($res);
			else $this->count = @mysqli_affected_rows($this->db_conn);

			$time[] = $this->microtime_float();
			$this->time[] = $time[1] - $time[0];
			$this->log[] = $query;

			$this->id = mysqli_insert_id($this->db_conn);
			return $res;
		}
	}

	function fetch($res,$mode=0){
		if (!is_object($res)) $res = $this->query($res);
		return (!$mode) ? @mysqli_fetch_assoc($res) : @mysqli_fetch_array($res);
	}

	function fetcharr($res){
		$loop = array();
		if (!is_resource($res)) $res = $this->query($res);
		while ($tmp=$this->fetch($res)){
			$loop[] = $tmp;
		}
		return $loop;
	}

	function count_($result){
        $rows = mysqli_num_rows($result);
        if ($rows == null) $rows = 0;
		return $rows;
    }

	function close(){
        $ret = @mysqli_close($this->db_conn);
        $this->db_conn = null;
        return $ret;
    }

	function error($err){
		if ($this->error_pass){
			$this->error = 1;
			return;
		}
		echo "
		<div style='padding:2'>
		<table width=100% border=1 bordercolor='#cccccc' style='border-collapse:collapse;font:9pt Courier New'>
		<col width=100 style='padding-right:10;text-align:right;font-weight:bold'><col style='padding:3 0 3 10'>
		<tr><td bgcolor=#f0f0f0>error</td><td>".mysqli_error($this->db_conn)."</td></tr>
		";
		foreach ($err as $k=>$v) echo "<tr><td bgcolor=#f0f0f0>$k</td><td>$v</td></tr>";
		echo "</table></div>";
		exit();
	}

	function viewLog(){
		echo "
		<table width=800 border=1 bordercolor='#cccccc' style='border-collapse:collapse;font:8pt tahoma'>
		<tr bgcolor='#f7f7f7'>
			<th width=40 nowrap>no</th>
			<th width=100%>query</th>
			<th width=80 nowrap>time</th>
		</tr>
		<col align=center><col style='padding-left:5'><col align=center>
		";
		foreach ($this->log as $k=>$v){
			echo "
			<tr>
				<td>".++$idx."</td>
				<td>$v</td>
				<td>{$this->time[$k]}</td>
			</tr>
			";
		}
		echo "
		<tr bgcolor='#f7f7f7'>
			<td>total</td>
			<td></td>
			<td>".array_sum($this->time)."</td>
		</tr>
		</table>
		";
	}

	function microtime_float(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}

?>