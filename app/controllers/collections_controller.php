<?php

class CollectionsController extends Kea_Action_Controller
{
	public function __construct()
	{
		$this->attachBeforeFilter(
			new RequireLogin( array( '_edit' => '10' ) )
		);
		
		$this->attachBeforeFilter(
			new RequireLogin( array( '_delete' => '10' ) )
		);
	}
	
	protected function _add()
	{
		if( self::$_request->getProperty( 'collection_add' ) ) {
			if( $this->commitForm() ) {
				$this->redirect( BASE_URI . DS . 'collections' . DS . 'all' );
				return;
			}
		}
		$c = new Collection;
		$c->collection_active = '1';
		return $c;
	}
	
	protected function _edit()
	{
		if( self::$_request->getProperty( 'collection_edit' ) ) {
			if( $this->commitForm() ) {
				$this->redirect( BASE_URI . DS . 'collections' . DS . 'all' );
				return;
			}
		} else {
			return $this->_findById();
		}
	}
	
	private function commitForm()
	{
		$collection = new Collection( self::$_request->getProperty( 'collection' ) );

		if( $this->validates( $collection ) ) {
			return $collection->save();
		}
		return false;
	}
	
	protected function _delete()
	{
		if( $id = self::$_request->getProperty( 'collection_id' ) ) {
			
			/**
			 * This will delete all the items in the collection if the right $_request variable is set,
			 * for obvious reasons only a super user should be able to do this.
			 *
			 */
			/* if( self::$_session->isSuper() )
			{
				$deleteItems = self::$_request->getProperty( 'delete_items' );
				if($deleteItems)
				{
					$objMapper = new Item_Mapper;
					$items = $objMapper->find()->where('collection_id = ?', $id)->execute();
					foreach($items as $item)
					{
						$item->delete();
					}
				}
			} */
			
			$mapper = new Collection_Mapper;
			$mapper->delete( $id );
			
			
			$this->redirect( BASE_URI . DS . 'collections' . DS . 'all' );
		}
	}
	
	protected function _findById( $id = null)
	{
		if( !$id )
		{
			$id = self::$_request->getProperty( 'id' ) ?
					self::$_request->getProperty( 'id' ) : 
						(isset( self::$_route['pass'][0] ) ?
						self::$_route['pass'][0] :
						0);	
		}

		$mapper = new Collection_Mapper();

		return $mapper->find()
					  ->where( 'collection_id = ?', $id )
					  ->execute();
	}
	
	protected function _all( $type = 'object')
	{
		$mapper = new Collection_Mapper;
		switch( $type ) {
			case( 'object' ):
				return $mapper->allObjects();
			break;
			case( 'array' ):
				return $mapper->allArray();
			break;
		}
		
		return false;
	}
	
	protected function _findActive()
	{
		$mapper = new Collection_Mapper();
		return $mapper->find()
					  ->where( 'collection_active = ?', '1' )
					  ->execute();
	}
	
	protected function _addToCollection()
	{
		$obj_id = self::$_request->getProperty( 'item_id' );
		$coll_id = self::$_request->getProperty( 'collection_id' );

		$mapper = new Collection_Mapper();
		if( empty($obj_id) || empty($coll_id) ) {
			self::$_session->flash('Please choose a collection to assign the items.');
			return null;
		} 
		return $mapper->addToCollection( $obj_id, $coll_id );
	}
	
}

?>