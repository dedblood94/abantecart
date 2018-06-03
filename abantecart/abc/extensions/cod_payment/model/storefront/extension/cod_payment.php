<?php 
class ModelExtensionDefaultCOD extends Model {
  	public function getMethod($address) {
		$this->load->language('cod_payment/cod_payment');
		if ($this->config->get('cod_payment_status')) {
			$query = $this->db->query("SELECT * FROM " . $this->db->table("zones_to_locations") . " WHERE location_id = '" . (int)$this->config->get('cod_payment_location_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
      		if (!$this->config->get('cod_payment_location_id')) {
        		$status = TRUE;
      		} elseif ($query->num_rows) {
        		$status = TRUE;
      		} else {
        		$status = FALSE;
      		}
		} else {
			$status = FALSE;
		}
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'id'         => 'cod_payment',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('cod_payment_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
