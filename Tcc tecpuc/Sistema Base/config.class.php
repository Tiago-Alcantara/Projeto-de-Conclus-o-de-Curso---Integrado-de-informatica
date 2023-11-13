<?php
	class config {
		protected $mysqli;
		
		public function __construct() {
			$this->mysqli = new mysqli('localhost','root','usbw','projeto');
		}
		
		function totalUsers() {
			$sql = "select count(*) as total from user";
			
			$query = $this->mysqli->query($sql);
			
			$result = $query->fetch_assoc();
			
			return $result['total'];
		}
		
		function encode($senha) {
			$array = str_split($senha);
			foreach($array as $hash) {
			$hashed = hash('sha256', $senha);
				$senha = $hashed;
			}
			
			return $senha;
		}
	}
?>