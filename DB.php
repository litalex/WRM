<?
class DBWrapper
{
	private $connect_string = '';
	private $persistent = false;
	private $sqlca;

//==============================================================================
//  constructor
//==============================================================================

//------------------------------------------------------------------------------
	function __construct( $connect_string, $persistent = false )
//------------------------------------------------------------------------------
	{
		$this->connect_string = $connect_string;
		$this->persistent = $persistent;
	}

//------------------------------------------------------------------------------
	function __destruct()
//------------------------------------------------------------------------------
	{
		if( !empty($this->sqlca) && !$this->persistent ) 
		{
			sasql_disconnect( $this->sqlca ); 
		}
	}
//------------------------------------------------------------------------------
	function __get( $name )
//------------------------------------------------------------------------------
	{
		if( isset($this->$name) )
		{
			return $this->$name;
		}
		elseif (method_exists($this,'get_'.$name))
		{
			call_user_func(array(&$this,'get_'.$name));
			return $this->$name;
		}
		else
		{
			throw new Exception( 'undefined get_'.$name.'()' );
			return false;
		}
	}

//==============================================================================
//  operations
//==============================================================================

//------------------------------------------------------------------------------
	public function process( $sql, &$error = '' )
//------------------------------------------------------------------------------
	{
		$this->check_db();
    if( !sasql_query( $this->sqlca, $sql, SASQL_STORE_RESULT ) )
    {
      $error = $this->error();
			return false;
    } else {
      return true;
    }
//		if( !sasql_execute( $this->sqlca, $sql ) ) $this->error();
	}

//------------------------------------------------------------------------------
	public function affected_rows( )
//------------------------------------------------------------------------------
	{
    return sasql_affected_rows( $this->sqlca );
  }
//------------------------------------------------------------------------------
	public function commit( )
//------------------------------------------------------------------------------
	{
//		echo "commit 1<br>";
		$this->check_db();
		if( !sasql_commit( $this->sqlca ) ) $this->error();
	}

//------------------------------------------------------------------------------
	public function get( &$sql, $cached=0, &$error = '' )
//------------------------------------------------------------------------------
	{
    $out = array();
    if( $cached ) // запрос с воз
    {
      $hash = md5( $sql );
      if( file_exists( 'cache/db_cache/'.$hash ) )
      {
        $expire = file_get_contents('cache/db_cache/'.$hash, false, NULL, 12, 10);
        // проверим результаты по ключу и их актуальность по времени
        $now = time();
        if( $expire > $now )
        {
          $out = $this->load_array_dump( 'cache/db_cache/'.$hash );
          return $out;
        }
      }
    }

		$this->check_db();
    if( !$query = sasql_query( $this->sqlca, $sql, SASQL_USE_RESULT ) )
		{
      $error = $this->error();
      return;
		}

		while($res = sasql_fetch_array( $query ) )
		{
			$out[] = $res;
		}
		sasql_free_result( $query );

    if( $cached ) // запрос с воз
    {
      $hash = md5( $sql );
      // проверим результаты по ключу
      $this->save_array_dump( 'cache/db_cache/'.$hash, $out );
    }
		return $out;
	}

//------------------------------------------------------------------------------
	public function page_get( &$sql, $page, $items_on_page, &$error = '' )
//------------------------------------------------------------------------------
	{
    $out = array();
    $hash = md5( $sql );
    if( file_exists( 'cache/db_cache/'.$hash.'_'.$page ) )  // если у нас есть закешированный результат запроса
    {
      $expire = file_get_contents('cache/db_cache/'.$hash.'_'.$page, false, NULL, 12, 10);
      // проверим результаты по ключу и их актуальность по времени
      $now = time();
      if( $expire > $now )
      {
        $out = $this->load_array_dump( 'cache/db_cache/'.$hash.'_'.$page );
        return $out;
      }
    }

		$this->check_db();
    if( !$query = sasql_query( $this->sqlca, $sql, SASQL_USE_RESULT ) )
		{
      $error = $this->error();
			return;
		}
		while($res = sasql_fetch_array( $query ) )
		{
			$out[] = $res;
		}
		sasql_free_result( $query );

    // положим результат в кеш
    // разбить результат на куски и каждый кусок положить как отдельный файл.
    $chunked_result = array_chunk($out, $items_on_page);
    $total = count( $out );
    $i = 1;
    $return = array();
    foreach( $chunked_result as $result_page )
    {
      if( $page == $i )
        $return = $result_page;
      $this->save_array_dump( 'cache/db_cache/'.$hash.'_'.$i, $result_page, $total );
      ++$i;
    }

    // вернуть нужную страницу
	  return array( 'page' => $return, 'total' => $total );
	}
  
  
  
  
  
  
  //==================================================================================================
  //    edited prev func - not cached
  //==================================================================================================

  //------------------------------------------------------------------------------
	public function page_get_new( &$sql, $page, $items_on_page, &$error = '' )
//------------------------------------------------------------------------------
	{
    $out = array();
    $hash = md5( $sql );

		$this->check_db();
    if( !$query = sasql_query( $this->sqlca, $sql, SASQL_USE_RESULT ) )
		{
      $error = $this->error();
			return;
		}
		while($res = sasql_fetch_array( $query ) )
		{
			$out[] = $res;
		}
		sasql_free_result( $query );

    // положим результат в кеш
    // разбить результат на куски и каждый кусок положить как отдельный файл.
    $chunked_result = array_chunk($out, $items_on_page);
    $total = count( $out );
    $i = 1;
    $return = array();
    foreach( $chunked_result as $result_page )
    {
      if( $page == $i )
        $return = $result_page;
      ++$i;
    }

    // вернуть нужную страницу
	  return array( 'page' => $return, 'total' => $total );
	}
  
  
  
  
  
  
  
//==============================================================================
//  implementation
//==============================================================================

//------------------------------------------------------------------------------
  private function error(  )
//------------------------------------------------------------------------------
{
  return sasql_error( $this->sqlca );
//  throw new Exception (sasql_error( $this->sqlca ) );
}

//------------------------------------------------------------------------------
  private function check_db( )
//------------------------------------------------------------------------------
	{
		if(isset($this->sqlca))return;
		$this->sqlca = $this->persistent ? sasql_pconnect( $this->connect_string ) : sasql_connect( $this->connect_string );
		if(empty($this->sqlca))
			//throw new Exception( 'Connecting error. '.sasql_error() );
      die( 'Connecting error. '.sasql_error() );
	}

//------------------------------------------------------------------------------
  private function load_array_dump($filename)
//------------------------------------------------------------------------------
  {
    $content = file_get_contents( $filename );
    $meta = substr( $content, 0, strpos($content, "\n") );
    $page = substr( $content, strpos($content, "\n")+1 );
//    $content = file_get_contents($filename, false, NULL, 26 ); //fread($fp,filesize($filename));

    eval('$array='.gzuncompress(stripslashes($page)).';');  // подготовить страницу
    // подготовить метаданные: сколько всего в наборе элементов
    $total = substr( $meta, strpos($meta, ";total:")+7, strpos($meta, ";total:") + 7 - strpos($meta, "-->") );
    return array( 'page' => $array, 'total' => $total );
  }

//------------------------------------------------------------------------------
  private function save_array_dump($filename, $array, $total) 
//------------------------------------------------------------------------------
  {
    $dump = addslashes(gzcompress(var_export($array,true),9));
    $expires = time() + CACHE_TIME;
		$timestamp = "<!--expires:{$expires};total:{$total}-->\n";
    file_put_contents($filename, $timestamp.$dump);
  }
}
?>