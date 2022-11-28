<?php
	
class CheckTapo
{
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle($ip, $login, $password)
    {
		exec('/usr/bin/python3 ' . __DIR__ . '/tapo.py "' . $ip . '" "' . $login . '" "' . $password . '"', $output, $retval);
//        exec('/usr/local/bin/python3 ' . __DIR__ . '/tapo.py "' . $ip . '" "' . $login . '" "' . $password . '"', $output, $retval);

		if($retval == 0 &&
			is_array($output) &&
			isset($output[0]))
		{
			$val = $output[0];
			$val = str_replace("'", "\"", $val);
			
			$data = json_decode($val, true);
			
			if(isset($data["result"]["today_energy"]) &&
				isset($data["result"]["current_power"]))
			{
				return [
					'power' => $data["result"]["current_power"] / 1000,
					'total' => $data["result"]["today_energy"] / 1000
				];
			}
			
			return null;
		}
	}
}