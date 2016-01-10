<?php
class x2
{
    protected $db       = null;
    private $database_host  = 'localhost';
    private $database_user  = 'root';
    private $database_pass  = '';
    private $database_db    = 'test';
    private $database_type  = 'mysql';
    public $stmt     = null;
    
    public function __construct(){
        if ($this->db === false) {
            $this->connect();
        }
    }
    
    private function connect(){
        $dsn = $this->database_type . ":dbname=" . $this->database_db . ";host=" . $this->database_host;
        try {
            $this->db = new PDO($dsn, $this->database_user, $this->database_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {        
            throw $e;
            //your log handler
        }
    }
    
    public function query($query, array $params = array()){ 
        try{ 
            $this->connect(); 
            $this->stmt        = $this->db->prepare($query); 
            $this->bind($query, $params); 
            $this->stmt->execute(); 
            return true; 
        }catch(Exception$e){ 
            throw $e; 
        } 
    }
    
    public function getAll($query, array $params = array()){ 
        $this->query($query, $params); 
        $array = $this->stmt->fetchAll(PDO::FETCH_ASSOC); 
        return $array; 
    }
    
    protected function bind($query, array $params){ 
        if(strpos($query, "?")){ 
            array_unshift($params, null); 
            unset($params[0]); 
        } 
        
        foreach($params as $key => $val){ 
            switch(gettype($val)){ 
                case "boolean": 
                    $type = PDO::PARAM_BOOL; 
                    break; 
                case "integer": 
                    $type = PDO::PARAM_INT; 
                    break; 
                case "string": 
                    $type = PDO::PARAM_STR; 
                    break; 
                case "null": 
                    $type = PDO::PARAM_NULL; 
                    break; 
                default: 
                    $type = PDO::PARAM_STR; 
                    break; 
            } 
            $this->stmt->bindValue($key, $val, $type); 
        } 
    }
    
    public function getInsertID(){ 
        return $this->db->lastInsertId(); 
    }
    
    public function parseXML($url){
    	$this->xml	= '';
    	$xmlString = file_get_contents ($url);
    	$x 	= simplexml_load_string ($xmlString);
    	$this->xml	= $x;
    }
    
    public function saveBasicData(){
    	$query  = "INSERT INTO `tbl_objects` (`object_id`,`object_name`,`object_type`) VALUES (?, ?, ?)";
    	$data	= array(
    			$this->xml->Id, 
    			$this->xml->Name, 
    			$this->xml->Type);
    	
    	$res        	= $this->query($query, $data);
    	$this->objId	= $this->getInsertID();
    }

    public function saveImages(){
    	$query    = "INSERT INTO `tbl_images`(`obj_id`,`image`) VALUES (?,?)";

    	foreach($this->xml->Photo as $pic){
	    	$data	= array($this->objId, $pic->Url);
	    	$res    = $this->query($query, $data);
    	}
    }

    public function saveAmenities(){
    	$query    = "INSERT INTO `tbl_amenities`(`obj_id`, `name`) VALUES (?,?)";
    
    	foreach($this->xml->Amenity as $pic){
	    	$data	= array($this->objId, $pic->Text);
	    	$res    = $this->query($query, $data);
    	}
    }
    
	
    public function saveDescriptions(){

    }
    
    public function getData($url){
    	$this->parseXML($url);
    	
    	if($this->xml){
    		$this->saveBasicData();
   			$this->saveAmenities();
   			$this->saveImages();
   			$this->saveDescriptions();
    	}
    }
}

$ob	= new x2();
$ob->getData("121223.xml"); 
?>