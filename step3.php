<?

  function seo_step( $db, &$C, $u_user, $u_site )
  {
    $C["step"] = $_GET["step"];
    $C["base"] = "?cm=seo";

    //SAVING PAGE INFO and maybe reidrecting
    if ( (isset( $_POST["action"] ) && $_POST["action"] == "save") OR (isset( $_GET["action"] ) && $_GET["action"] == "save") )
    {
      $title = addslashes( trim( htmlspecialchars( $_POST["metatitle"] )));
      $descr = addslashes( trim( htmlspecialchars( $_POST["descr"] )));


      $kwrds = false;
      //pasiimam visus keywordus i masyva
      $keywords = array();
      for ( $i=1;$i<=21;$i++ )
      {
        if( $_POST[ "key" . $i ] != "" )
        {
            $kwrds = true;
        }
        $keywords[] = addslashes( htmlspecialchars( $_POST[ "key" . $i ])) ;
      }

    //  print "<!-- $title, $descr, $kwrds\n\n";

    //  print_r($keywords);

    //  print " -->";

      // checking post
      if( $title == "" )
      {
        header("Location: ".($C["base"])."&step=". ($C["step"]) );
        exit();
      }
      if( $descr == "" )
      {
        header("Location: ".($C["base"])."&step=". ($C["step"]) );
        exit();
      }
      if( $kwrds == false )
      {
        header("Location: ".($C["base"])."&step=". ($C["step"]) );
        exit();
      }


      //sudedam keywordus ish masyvo i stringa
      $keywordstring = implode ("|", str_replace( "|", "", $keywords ) );

      //storinam title descr ir keywordsus i duombaze.

      $data = array();
      $data["id_u_site"]     = $u_site["id_u_site"];
      $data["meta_title"]    = $title;
      $data["meta_descr"]    = $descr;
      $data["meta_keywords"] = $keywordstring;

      $test = $db->GetRecord( "SELECT * FROM as_seo WHERE id_u_site = ".$u_site["id_u_site"] );

      if($test)
      $db->UpdateRecord( "as_seo", $data, "id_u_site", $u_site["id_u_site"]);
      else
        $db->InsertRecord( "as_seo", $data );


      // if proceed pressed
      if( $_POST["redirect"] == true )
      {
///        $db->UpdateRecord("as_seo", array("step{$C["step"]}" => 1, "step{$C["step"]}_updated" => time() ), "id_u_site", $u_site["id_u_site"] );
//        trigger ($db, $u_site["id_u_site"], "STEP2-COMPLETED", "SEO" );
        header("Location: ".($C["base"])."&step=". ($C["step"]) );
        exit();
      }
      //else
      header( "Location: ".($C["base"])."&step={$C["step"]}"); exit();

    }
    //DISPLAYING WEB PAGE
    else
    {
      if( isset($_GET["action"]) AND $_GET["action"] == "save_meta" AND isset($_GET["page"]) AND isset($_GET["id"]) )
      {
        $page = $_GET["page"];
        $id_u_site_meta = intval( $_GET["id"] );

       $update["meta_title"] = addslashes( htmlspecialchars( trim($_POST["metatitle"]) ));
        $update["meta_description"] = addslashes(htmlspecialchars(trim( $_POST["descr"] )));
        $update["meta_keywords"] = "";
        $kwrds = false;
        //pasiimam visus keywordus i masyva

        for ( $i=1;$i<=21;$i++ )
        {
          if( /* $_POST[ str_replace(" ", "_", $page)."_key".$i ] != "" AND */ $_POST[ str_replace(" ", "_", $page)."_key".$i ] != "my keyword 1" )
          {
              $update["meta_keywords"] = $update["meta_keywords"].addslashes( htmlspecialchars( $_POST[ str_replace(" ", "_", $page)."_key".$i ])).", " ;
          }
        }

      if( $db->UpdateRecord("u_site_meta", $update, "id_u_site_meta", $id_u_site_meta, " AND id_u_site = ".$u_site["id_u_site"]) )
        $C["msg"] = '<h1 style="color:green">'.$page.' Page meta saved.</h1>';
      else
        $C["msg"] = '<h1 style="color:red">Failed to save '.$page.' Page meta.</h1>';

      }

      //getting all meta info about current web site
      $C["metainfo"] = $db->GetRecord ( "SELECT * FROM `as_seo` WHERE `id_u_site` = '{$u_site["id_u_site"]}';" );

      if( $C["metainfo"]["step". ($C["step"] - 1)] == 0)
      {
        //header( "Location: ".($C["base"])."&step=3" );
        //exit();
      }

      if( $C["metainfo"] && $C["metainfo"]["meta_keywords"] != ""  )
      {
        $exploded = explode("|", $C["metainfo"]["meta_keywords"] );
        for ( $i=0;$i<=20;$i++ )
        {
         $C["key" . ($i+1) ] = $exploded[ $i ];
        }
      }


      /////////////////////////////////////////////////////////////////////////
      // ONGOING SEO META
      $test = $db->GetTable("SELECT * FROM u_site_meta WHERE id_u_site = {$u_site["id_u_site"]} AND allow = 1 GROUP BY `page`");
      if( $test )
      {
        $C["hide_button"] = true;
        foreach( $test as $el )
        {
            $exploded = explode(",", $el["meta_keywords"] );
            for ( $i=0;$i<=20;$i++ )
            {
             $value = isset($exploded[ $i ]) ? trim( $exploded[ $i ] ) : "" ;
             $C[$el["page"]."_key" . ($i+1) ] = $value;
             $el["keyword_set"][] = $value;
            }
            $C["meta_pages"][] = $el;
        }
      }
      /////////////////////////////////////////////////////////////////////////
    }
  }

?>
