<?php
class Admin_UniversityController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	$this->_helper->layout()->setLayout('admin');
    }
	/**
	 * Listado de universidades
	 */
    public function indexAction()
    {
    	$this->view->headTitle('University :: List');
    	$University = new Table_University();
    	
    	$select = $University->select()
	    			->setIntegrityCheck(false)
	    			->from(array('r0'=> 'University'), array('r0.id', 'r0.uniName'))
	    			->joinLeft( array('r1'=> 'School') , 'r0.id = r1.idUniversity', array('r1.schName'))
	    			->joinLeft( array('r2'=> 'Department') , 'r1.id = r2.idSchool', array('r2.depName'))
	    			;
    	$universitys = $University->getAdapter()->fetchAll( $select ) ;
    			
    	$uni = array();
	    foreach($universitys as $value){ 
	    	$uni[$value["id"]][$value["uniName"]][$value["schName"]][] = $value["depName"];
	    }
	    
	    $this->view->assign('University', $uni );
    			
    }
    
    /**
     * Guardar school por ajax (nueva o edit)
     * @throws Exception
     */
    public function saveschoolAction(){
    	$this->_helper->layout()->setLayout('empty');
    	$this->_helper->viewRenderer->setNoRender();
    	$School = new Table_School();
    	try{
    		$Id = $this->getRequest()->getParam('Id', null);
    		$data	= $this->getRequest()->getParam('data', null);
    		
    		$schoolId = $this->getRequest()->getParam('schoolId', null);
			if (is_null($schoolId) || empty($schoolId)){
				$school = $School->createRow();
			} else {
				$school = $School->fetchRow('Id = ' . $schoolId );
			}
			
    		$nombre = null;
    		if(!empty($data)){
    			foreach($data as $dato){
					if($dato['campo']=='schName'){
						$nombre = $dato['valor'];
					}
				}
								
				$school->schName = $nombre;
				$school->idUniversity = $Id;
				$id = $school->save();
				echo Zend_Json_Encoder::encode(array('schoolId'=> $id, 'schName'=>$nombre));
    		}
				
    	} catch (Exception $e){
    		$this->view->assign( 'error', $e->getMessage() );
    		$this->_forward('index', 'university', 'admin');
    	}
    	
    }
    
    /**
     * Guardar deppartment por ajax (nuevo o edit)
     * @throws Exception
     */
	public function savedeppartmentAction(){
    	$this->_helper->layout()->setLayout('empty');
    	$this->_helper->viewRenderer->setNoRender();
    	$Deppartment = new Table_Department();
    	try{
    		$Id = $this->getRequest()->getParam('Id', null);
    		$data	= $this->getRequest()->getParam('data', null);
    		
    		$deppId = $this->getRequest()->getParam('deppId', null);
			if (is_null($deppId) || empty($deppId)){
				$deppartment = $Deppartment->createRow();
			} else {
				$deppartment = $Deppartment->fetchRow('Id = ' . $deppId );
			}
			
    		$nombre = null;
    		if(!empty($data)){
    			foreach($data as $dato){
					if($dato['campo']=='depName'){
						$nombre = $dato['valor'];
					}
				}
								
				$deppartment->depName = $nombre;
				$deppartment->idSchool = $Id;
				$id = $deppartment->save();
				echo Zend_Json_Encoder::encode(array('deppId'=> $id, 'depName'=>$nombre));
    		}
				
    	} catch (Exception $e){
    		$this->view->assign( 'error', $e->getMessage() );
    		$this->_forward('index', 'university', 'admin');
    	}
    	
    }
    
     /**
     * Edit university
     * @throws Exception
     */
    
    public function edituniversityAction(){ 
    	
    	$University = new Table_University();
    	$School = new Table_School();
    	$Department = new Table_Department();
    	
    	try{
    		$Id = $this->getRequest()->getParam('id', null); 
    		$accion = $this->getRequest()->getParam('accion', null);
    		$where = "1=1";
    		
    			if(!empty($Id)){
    			
		    		$select = $School->select()
			    			->setIntegrityCheck(false)
			    			->from(array('r0'=> 'School'), array('r0.id', 'r0.schName'))
			    			->joinLeft( array('r1'=> 'Department') , 'r0.id = r1.idSchool', array('r1.depName', 'r1.id as depid'))
			    			->where('r0.idUniversity = ?', $Id)
			    			;
			    	$schools = $School->getAdapter()->fetchAll( $select ) ;
			    			
			    	$sc = array();
				    foreach($schools as $value){ 
				    	$sc[$value["id"]][$value["schName"]][$value["depid"]][] = $value["depName"];
				    }
			    	$this->view->assign('School', $sc );
			        $this->view->assign('University', $University->fetchRow($University->select()->where('id = ?', $Id)));
    			}
    			
    		if(!is_null($Id) && is_null($accion)) { 
    			$this->view->headTitle('University :: Edit');
    			$this->view->assign('accion', 'edit');
    		}
    		if(is_null($Id)) { 
    			$this->view->headTitle('University :: Add');
    			$this->view->assign('accion', 'add');
    			$this->view->assign('id', null);
    			$this->view->assign('University', null);
    		}	
    			
    	} catch (Exception $e){
    		$this->view->assign( 'error', $e->getMessage() );
    		$this->_forward('index', 'university', 'admin');
    	}    	
    }
    
 /**
     * ajax para eliminar universities
     * @throws Exception
     */
    
    public function deleteuniAction(){
    	$this->_helper->layout()->setLayout('empty');
    	$this->_helper->viewRenderer->setNoRender();
    	$University = new Table_University();
    	   	
    	try {
			$Id = $this->getRequest()->getParam('Id', null);
			if( is_null($Id) ){
				throw new Exception( 'Insufficient parameters' );
			}
			$resp = $University->delete("Id = $Id");
			echo Zend_Json_Encoder::encode(array('Id'=> $Id));//$this->_forward('index', 'university', 'admin');//echo Zend_Json_Encoder::encode(array('Id'=> $Id));
		} catch (Exception $e) {
			echo $e->getMessage();
		}	
    }
    
    /**
     * ajax para eliminar schools
     * @throws Exception
     */
    
    public function deleteschoolAction(){
    	$this->_helper->layout()->setLayout('empty');
    	$this->_helper->viewRenderer->setNoRender();
    	$School = new Table_School();
    	
    	try {
			$Id = $this->getRequest()->getParam('Id', null);
			if( is_null($Id) ){
				throw new Exception( 'Insufficient parameters' );
			}
			
			$resp = $School->delete("Id = $Id");
			echo Zend_Json_Encoder::encode(array('Id'=> $Id));
		} catch (Exception $e) {
			echo $e->getMessage();
		}	
    }
    /**
     *ajax para eliminar deppartments
     * @throws Exception
     */
	public function deletedeppAction(){
    	$this->_helper->layout()->setLayout('empty');
    	$this->_helper->viewRenderer->setNoRender();
    	$Depp = new Table_Department();
    	
    	try {
			$Id = $this->getRequest()->getParam('Id', null);
			if( is_null($Id) ){
				throw new Exception( 'Insufficient parameters' );
			}
			
			$resp = $Depp->delete("Id = $Id");
			echo Zend_Json_Encoder::encode(array('Id'=> $Id));
		} catch (Exception $e) {
			echo $e->getMessage();
		}	
    }
    
    /**
     * Ajax que guarda la información del formulario de universidades
     * @throws Exception
     */
    public function saveuniversityAction(){
    	$this->_helper->layout()->setLayout('empty');
    	$this->_helper->viewRenderer->setNoRender();
    	$University = new Table_University();
    	// Obtener parámetros
    	$tr = $University->getAdapter()->beginTransaction();
    	try {
    		$Id 	 = $this->getRequest()->getParam('id'		, null);
    		$accion  = $this->getRequest()->getParam('accion'	, null);
    		$data	 = $this->getRequest()->getParam('data'		, null);
   		
    		if( !is_null($accion) && !is_null($data) ){ // editar o añadir
    			$UniversityRow = null;
    			
    			if (is_null($Id) || empty($Id)){ 				
    				$UniversityRow = $University->createRow();
       			} else {
    				$UniversityRow = $University->fetchRow($University->select()->where('id = ?', $Id));
    			}

    			foreach($data as $dato){ 
    					if($dato['campo']== 'uniName'){
    						$UniversityRow->$dato['campo'] = $dato['valor'];
    						$nombre = $dato['valor'];
    					}
    			}
    
    			if($accion == 'edit'){
    				$idUn = $Id;
    				$UniversityRow->save();
    			}else{
    				$idUn = $UniversityRow->save();
    			}
	    			
    			$tr->commit();
    			echo Zend_Json_Encoder::encode(array('id'=> $idUn, 'uniName'=>$nombre));
    		} else {
    			throw new Exception("Insufficient parameters");
    		}
    
    	} catch (Exception $e){
    		$tr->rollBack();
    		echo( $e->getMessage() );
    	}
    }       
}