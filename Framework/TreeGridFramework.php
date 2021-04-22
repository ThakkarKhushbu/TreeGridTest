<?php
// -------------------------------------------------------------------------------------------
// TreeGrid Framework for accessing database
// It contains two basic functions:
//    String LoadXMLFromDB()  Returns data in XML, from database for TreeGrid 
//    SaveXMLToDB(String XML) Saves changes in XML from TreeGrid to database
// TreeGrid can be created by 
//    new TreeGrid(Database, DBTable, DBIdCol, IdPrefix, DBParentCol, DBDefCol)
//       Database = ""         // Required - Database object to access the SQL database, from appropriate IncDb...php file
//       DBTable = ""          // Required - Table name in database
//       DBIdCol = "ID"        // Column name in database table where are stored unique row ids
//       IdPrefix = ""         // Prefix added in front of id, used if ids are number type, the same prefix must be in Layout <Cfg IdPrefix=''/>
//       DBParentCol = ""      // Column name in database table where are stored parent row ids, if is empty, the grid does not contain tree
//       DBDefCol = ""         // Column name in database table where are stored Def parameters (predefined values in Layout, used usually in tree)

// -------------------------------------------------------------------------------------------

require_once("IncDbMySQL.php");


class TreeGrid {
   
var $DBTable,$DBIdCol,$IdPrefix,$DBParentCol,$DBDefCol,$db;

function TreeGrid($db, $dbTable, $dbIdCol,$idPrefix,$dbParentCol,$dbDefCol) {
  $this->__construct($db, $dbTable, $dbIdCol,$idPrefix,$dbParentCol,$dbDefCol);
  register_shutdown_function(array($this,"__destruct"));
  }

function __construct($db, $DBTable, $DBIdCol,$IdPrefix,$DBParentCol,$DBDefCol){
   $this->db = $db;
   $this->DBTable = $DBTable;
   $this->DBIdCol = $DBIdCol ? $DBIdCol : "id";
   $this->IdPrefix = $IdPrefix ? $IdPrefix : "";
   $this->DBParentCol = $DBParentCol;
   $this->DBDefCol = $DBDefCol;
   } 
function __destruct(){ }



// -------------------------------------------------------------------------------------------
// Helper function for LoadXMLFromDB to read data from database table and convert them to TreeGrid XML
// Returns XML string width all children with Parent
// If Parent is null returns all rows (for non tree tables)

public function showChild($parentGridId = 0)
{
   $rows = $this->db->Query("SELECT * FROM `transfers` WHERE `Parent` = $parentGridId AND `Def` = 'Data'");
  $rows = $rows->GetRows();
      
   foreach ($rows as $row) {
      echo  " <I Level='1' Def='Data' id='" . $row["grid_id"] . "'"
         . " name='" . $row["name"] . "'"
         . " type='" . $row["type"] . "'"
         . " code='" . $row["code"] . "'"
         . " barcode='" . $row["barcode"] . "'"
         . " brand='" . $row["brand"] . "'"
         . " category='" . $row["category"] . "'"
         . " subcategory='" . $row["subcategory"] . "'"
         . " unit='" . $row["unit"] . "'"
         . " cost='" . $row["cost"] . "'"
         . " price='" . $row["price"] . "'"
         . " credit_quantity='" . $row["credit_quantity"] . "'"
         . " debit_quantity='" . $row["debit_quantity"] . "'"
         . " note='" . $row["note"] . "'"
         . " item_uuid='" . $row["item_uuid"] . "'"
         . " warehouse_origin_uuid='" . $row["warehouse_origin_uuid"] . "'"
         . " warehouse_destination_uuid='" . $row["warehouse_destination_uuid"] . "'"
         . " />";
   }
}



function GetChildrenXML($Parent){

$db = CustomPdo::make("TreeGridTest", "root", "", "localhost");

$limit = 30;
$page = isset($_GET['page']) && $_GET['page'] != 0 && is_numeric($_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 1;

$offset = ($page - 1) * $limit;

$statement = $db->prepare("SELECT * FROM transfers WHERE `Parent` = :parent  AND `has_child` = :has_child 
 AND `Def` =:Def ORDER BY ID DESC LIMIT :limit OFFSET :offset");

$rows = $db->execute($statement, [
    ':parent' => '0',
    ':has_child' => '1',
    ':limit' => $limit,
    ':Def' => 'Node',
    ':offset' => $offset
]);

$rows = $rows->GetRows();

echo "<Grid>
<Body><B Pos='" . $B["Pos"] . "'>";
$cnt = count($rows);
foreach ($rows as $row) {
   
   echo " <I data='test'  Level='0' Def='Node' id='" . $row["grid_id"] . "'"
      . " document_type='" . $row["document_type"] . "'"
      . " document_abbrevation='" . $row["document_abbrevation"] . "'"
      . " document_no='" . $row["document_no"] . "'"
      . " posting_date='" . $row["posting_date"] . "'"
      . " document_date='" . $row["document_date"] . "'"
      . " warehouse_origin='" . $row["warehouse_origin"] . "'"
      . " warehouse_origin_code='" . $row["warehouse_origin_code"] . "'"
      . " warehouse_destination='" . $row["warehouse_destination"] . "'"
      . " warehouse_destination_code='" . $row["warehouse_destination_code"] . "'"
      . " company='" . htmlentities($row["company"], ENT_QUOTES) . "'" //this
      . " company_vat_no='" . $row["company_vat_no"] . "'"
      . " credit_quantity='" . $row["credit_quantity"] . "'"
      . " debit_quantity='" . $row["debit_quantity"] . "'"
      . " warehouseman='" . htmlentities($row["warehouseman"], ENT_QUOTES) . "'" //this
      . " warehouseman_department='" . $row["warehouseman_department"] . "'"
      . " warehouseman_approve='" . $row["warehouseman_approve"] . "'"
      . " deliveryman='" . htmlentities($row["deliveryman"], ENT_QUOTES) . "'" //this
      . " deliveryman_department='" . $row["deliveryman_department"] . "'"
      . " deliveryman_approve='" . $row["deliveryman_approve"] . "'"
      . " warehouseman_destination='" . htmlentities($row["warehouseman_destination"], ENT_QUOTES) . "'" //this
      . " warehouseman_destination_department='" . $row["warehouseman_destination_department"] . "'"
      . " warehouseman_destination_approve='" . $row["warehouseman_destination_approve"] . "'"
      . " status='" . $row["status"] . "'"
      . " note='" . $row["note"] . "'"
      . " item_uuid='" . $row[   "item_uuid"] . "'"
      . " warehouse_origin_uuid='" . $row["warehouse_origin_uuid"] . "'"
      . " warehouse_destination_uuid='" . $row["warehouse_destination_uuid"] . "'"
      . " />";

     
   if ($row['has_child']) {
      
      $rows = $db->prepareAndExec("SELECT * FROM `transfers` WHERE `Parent` = :parent AND `Def` = :Def " , [
         ':parent' => $row["grid_id"],
         ':Def' => 'Data',
     ]);
     $rows = $rows->GetRows();
            
         foreach ($rows as $row) {
            echo  " <I Level='1' Def='Data' id='" . $row["grid_id"] . "'"
               . " name='" . $row["name"] . "'"
               . " type='" . $row["type"] . "'"
               . " code='" . $row["code"] . "'"
               . " barcode='" . $row["barcode"] . "'"
               . " brand='" . $row["brand"] . "'"
               . " category='" . $row["category"] . "'"
               . " subcategory='" . $row["subcategory"] . "'"
               . " unit='" . $row["unit"] . "'"
               . " cost='" . $row["cost"] . "'"
               . " price='" . $row["price"] . "'"
               . " credit_quantity='" . $row["credit_quantity"] . "'"
               . " debit_quantity='" . $row["debit_quantity"] . "'"
               . " note='" . $row["note"] . "'"
               . " item_uuid='" . $row["item_uuid"] . "'"
               . " warehouse_origin_uuid='" . $row["warehouse_origin_uuid"] . "'"
               . " warehouse_destination_uuid='" . $row["warehouse_destination_uuid"] . "'"
               . " />";
         }
   }
}

}



// -------------------------------------------------------------------------------------------
// Loads data from database table and returns them as XML string
function LoadXMLFromDB(){
$XML = "<Grid>";
if($this->DBParentCol != ""){
   $XML .= "<Head>" . $this->GetChildrenXML("#Head") . "</Head>";
   $XML .= "<Foot>" . $this->GetChildrenXML("#Foot") . "</Foot>";
   $XML .= "<Body><B>" . $this->GetChildrenXML("#Body") . "</B></Body>";
   }
else $XML .= "<Body><B>" . $this->GetChildrenXML(NULL) . "</B></Body>";
return $XML . "</Grid>";
}

// -------------------------------------------------------------------------------------------
function SaveXMLToDB($db, $XML) {

// --- simple xml or php xml --- 
$SXML = is_callable("simplexml_load_string");
if(!$SXML) require_once("Xml.php");
if($SXML){ 
   $Xml = simplexml_load_string(html_entity_decode($XML));
   $AI = $Xml->Changes->I;
   }
else { 
   $Xml = CreateXmlFromString(html_entity_decode($XML));
   $AI = $Xml->getElementsByTagName($Xml->documentElement,"I");
   }
foreach($AI as $I){
   $A = $SXML ? $I->attributes() : $Xml->attributes[$I];
   // --- end of simple xml or php xml ---     
   
   if(!empty($A["Deleted"])){
      $statement = $db->prepare("DELETE FROM transfers WHERE `$this->DBIdCol` = :project_id");
      $db->execute($statement, [
         ':project_id' => $A["id"]
      ]);
   }
      
   else if(!empty($A["Added"])){
      $Cols = "INSERT INTO " . $this->DBTable . "(";
      $Vals = ") VALUES (";
      foreach($A as $name => $value){
         if($name!="Added" && $name!="Changed" && $name!="Moved" && $name!="Next" && $name!="Prev" && $name!="Parent"){
            if($name=="id")
            { 
               $name = ''; 
               $val = $A["id"]; 
            }
            if($name=="Def") $name = $this->DBDefCol;
            if($name!=""){
               $Cols .= "$name,";
               $Vals .= "'" . str_replace("'","''",$value) . "',";
               }
            }
         }

       if($this->DBParentCol!=""){
          $Cols .= $this->DBParentCol;
          $Vals .= "'" . ($A["Parent"]!="" ? $A["Parent"] : "#Body") . "'";
          }
       else {
         $Cols = substr($Cols,0,strlen($Cols)-1);
         $Vals = substr($Vals,0,strlen($Vals)-1);
         }
      $this->db->Exec($Cols . $Vals . ")");

      // GETTING LAST INSERETED ID, UDATE GRID_ID
      $rows = $db->prepareAndExec("SELECT * FROM `transfers` ORDER BY `id` DESC LIMIT :limit" , [
         ':limit' => '1'
      ]);
      $rows = $rows->GetRows();
      foreach ($rows as $row) 
      {
         $idd = $row["id"];
         $Def = $row["Def"];
         if($Def == 'Node') 
         {
            $statement1 = $db->prepare("UPDATE `transfers` SET `has_child` = :has_child WHERE id = '$idd' and `Def` = 'Node'");
            $db->execute($statement1, [
               ':has_child' => 1
            ]);
         } 
         $statement2 = $db->prepare("UPDATE `transfers` SET `grid_id` = :grid_id WHERE id = '$idd'");
         $db->execute($statement2, [
            ':grid_id' => $idd
         ]);
         

      }
      }


   else if(!empty($A["Changed"]) || !empty($A["Moved"])){
      $S = "UPDATE " . $this->DBTable . " SET ";
      foreach($A as $name => $value){
         if($name!="Added" && $name!="Changed" && $name!="Moved" && $name!="Next" && $name!="Prev" && $name!="id") {
            if($name=="Parent"){
               if($value=="") $value = "#Body";
               if($this->DBParentCol!="") $S .= $this->DBParentCol." = '".str_replace("'","''",$value)."',";
               }
            else $S .= "$name = '".str_replace("'","''",$value)."',";
            }
         }
      $S = substr($S,0,strlen($S)-1);
      $S .= " WHERE " . $this->DBIdCol . "='".$A["id"]."'";
      $this->db->Exec($S);
      }
   }
}
// -------------------------------------------------------------------------------------------
}
?>