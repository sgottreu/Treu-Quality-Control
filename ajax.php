<?php

/*
Copyright (C) 2012 Scott gottreu

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

require_once('../../../wp-config.php');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$link) {
    die('Not connected : ' . mysql_error());
}

$db_selected = mysql_select_db(DB_NAME);
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());
}

switch ($_GET['action']) {
	case "getPersons":
		getPersons();
		break;
    case "createToDoItem":
        createToDoItem();
        break;
    case "createToDoItemComment":
        createToDoItemComment();
        break;
	case "getAllToDoItems":
		getAllToDoItems();
		break;
    case "getAllToDoLists":
        getAllToDoLists();
        break;
    case "getProjects":
        getProjects();
        break;
    case "getProjectToDoLists":
        getProjectToDoLists();
        break;
}

function connectBasecamp($url=false, $data=false, $args=array()) {
    $api_key = get_option( 'qc_basecamp_api' );
    $subdomain = get_option('qc_basecamp_subdomain');
    
	if(!function_exists("curl_init")) {
		$arr["error"] = "Curl is not installed";
		echo json_encode($arr);
		die;
	}
	
    $ch = curl_init("https://$subdomain.basecamphq.com/$url");
    
    curl_setopt($ch, CURLOPT_USERPWD, $api_key); 
    curl_setopt($ch,CURLOPT_HTTPHEADER,array (
            "Accept: application/xml",
            "Content-Type: application/xml",
        ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if($data) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    $page =  curl_exec($ch);
    curl_close($ch);
    
	$xml = new SimpleXMLElement($page);
	
    $i = 0;
	foreach($xml as $key => $r) {
		$array[$i] = new StdClass;
		
		foreach($r as $k => $col) {
			$k = str_replace('-','_',$k);
			$array[$i]->$k = (string)$col;
		}
		$i++;
	}	
	return $array;
}

function getProjects()
{
    $url = "/projects.xml";
    
    $items = connectBasecamp($url);
 
    $args = array('status' => true);

    $dropdown = createDropdown($items,'id','name',$args);

    echo $dropdown;
}

function getProjectToDoLists()
{
    $project_id = $_GET['projectid'];
    
    $url = "/projects/$project_id/todo_lists.xml?filter=all";
    
    $items = connectBasecamp($url);
    
    $args = array('completed' => true, 'truncate' => true);

    $dropdown = createDropdown($items,'id','name',$args);

    echo $dropdown;
}

function getAllToDoLists()
{
    $companyId = getBaseCampCompanyId();
    
    $url = "/todo_lists.xml?responsible_party= ";
    
    $items = connectBasecamp($url);

    $dropdown = createDropdown($items,'id','name');

    echo $dropdown;
}



function getAllToDoItems() {
    $id = $_POST['ToDoListId'];
    
    $url = "todo_lists/$id/todo_items.xml";
    
    $items = connectBasecamp($url);
 
    $args = array('completed' => true, 'truncate' => true);
    
    $dropdown = createDropdown($items,'id','content',$args);

    echo $dropdown;
}

function createToDoItem() {
    
    $todo = $_POST['comment'] . " -- " . $_POST['url'];
    
    $party_id = $_POST['responsible_party'];
    
    $id = $_POST['ToDoListId'];
        
    $url = "todo_lists/$id/todo_items.xml";
    
    $xmldata = "<todo-item>
            <content>$todo</content>
            <due-at>$due_at</due-at>
            <responsible-party>$party_id</responsible-party>
            <notify type=\"boolean\">true</notify>  
        </todo-item>";

      
    $result = connectBasecamp($url, $xmldata);
    
    echo $result;
}

function createToDoItemComment() {
    $body = $_POST['comment'] . " -- " . $_POST['url'];
    
    $id = $_GET['ToDoItemId'];
    
    $url = "todo_items/$id/comments.xml";
    
    $xmldata = "<comment>
            <body>$body</body> 
        </comment>";

      
    $result = connectBasecamp($url, $xmldata);
    
    echo $result; 
}

function getPersons()
{
    $companyId = get_option( 'qc_basecamp_company' );
        
    $url = "/companies/$companyId/people.xml";    
    
	$people = connectBasecamp($url);

	foreach($people as $key => $r) {
		$people[$key]->full_name = $people[$key]->last_name . ', ' . $people[$key]->first_name ;
	}

    usort($people, "sortLastFirst");       
    
    $dropdown = createDropdown($people,'id','full_name');

    echo $dropdown;
}


function createDropdown($xml,$value,$label,$args=array())
{
    $dropdown = '<option value="">Please choose</option>';
    
    foreach($xml as $key => $r) {

        if ($args['completed'] && $r->completed == 'true') { 
            continue;
        }
        if ($args['status'] && $r->status != 'active') { 
            continue;
        }
        if($args['truncate']) {
            $newLabel = substr($r->$label,0,40);
        } else {
            $newLabel = $r->$label;
        }
        
        $dropdown .= "<option value='" . $r->$value . "'>" . $newLabel . "</option>";
    }
    
    return $dropdown;
}


function sortLastFirst($a, $b) {
 
    if ($a->full_name == $b->full_name) {
        return 0;
    }
    return ($a->full_name < $b->full_name) ? -1 : 1;
}

