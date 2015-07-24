<?php

class DonationModel {
	
	protected static $instance;

	protected $db;

	private function __construct() {
		global $wpdb;

		$this->db = $wpdb;

		$this->db->donors = $wpdb->prefix . "donors";
	}

	private function __clone() {}

	public static function getInstance() {
		if( is_null(self::$instance) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function addDonation( $param, $format ) {

		if( $this->db->insert($this->db->donors, $param, $format) ) {
			return $this->db->insert_id;
		}

		return false;

	}

	public function txnIdExists( $txn_id ) {

		$sql = "SELECT COUNT(*) FROM {$this->db->donors} WHERE txn_id=%s";

		$count = $this->db->get_var($this->db->prepare($sql, $txn_id));

		return $count > 0;
	}

	public function getDonationById( $donation_id ) {
		$donation_id = absint($donation_id);

		$sql = "SELECT * FROM {$this->db->donors} WHERE id=%d";

		$row = $this->db->get_row($this->db->prepare($sql, $donation_id));

		return $row;
	}

	public function update( $data, $where, $data_format='%s', $where_format='%s') {

		if( FALSE !== $this->db->update( $this->db->donors, $data, $where, $data_format, $where_format ) ) {
			return true;
		}

		return false;
	}
	
	public function getDonations( $param = array() ) {	
	
		$sql = "SELECT * FROM {$this->db->donors} WHERE donate_status='paid'";
		$placeholders = array();
		
		if( isset($param["year"]) ) {
			$year = absint($param["year"]);
			
			$sql .= " AND YEAR(donor_date_donated)=$year";
		}		
		
		if( isset($param["month"]) ) {
			$month = absint($param["month"]);
			
			$sql .= " AND MONTH(donor_date_donated)=$month";
		}		
		
		$sql .= " ORDER BY donor_date_donated DESC";
		
		if( isset($param["offset"]) && isset($param["limit"]) ) {
			$offset = absint($param["offset"]);
			$limit = absint($param["limit"]);
			
			$sql .= " LIMIT $offset, $limit";
		}
		
		$results = $this->db->get_results($sql);
		
		return $results;
	}
	
	public function getTotalDonations( $param ) {
		$sql = "SELECT COUNT(*) FROM {$this->db->donors} WHERE donate_status='paid'";
		
		if( isset($param["month"]) ) {
			$month = absint($param["month"]);
			
			$sql .= " AND MONTH(donor_date_donated)=$month";
		}		
		
		if( isset($param["year"]) ) {
			$year = absint($param["year"]);
			
			$sql .= " AND YEAR(donor_date_donated)=$year";
		}		
		
		$count = $this->db->get_var($sql);
		
		return $count;
	}
	
	public function getDonatedYearMonths() {
		$sql = "SELECT YEAR(donor_date_donated) as year_donated, MONTH(donor_date_donated) as month_donated FROM {$this->db->donors} ORDER by year_donated DESC, month_donated ASC";
		
		$results = $this->db->get_results($sql);
		
		$group = array();
		
		if( count($results) > 0 ) {
			foreach( $results as $result ) {
				if( ! isset($group[$result->year_donated]) ) {
					$group[$result->year_donated] = array();
				}
				
				//if( ! isset($group[$result->month_donated]) ) {
				if( ! in_array($result->month_donated, $group[$result->year_donated]) ) {
					$group[$result->year_donated][] = $result->month_donated;
				}
			}
		}
		
		return $group;
	}

	public function deleteDonationById( $donation_id ) {

		if( false !== $this->db->delete($this->db->donors, array('id'=>$donation_id), '%d') ) {
			return true;
		}

		return false;
	}
}