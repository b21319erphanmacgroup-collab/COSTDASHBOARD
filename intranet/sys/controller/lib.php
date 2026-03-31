<? 
//========================================================================================= 
//  파일 업로드 함수 
// 
//  파라미터 
//    $upload_dir     : 업로드 드렉토리 
//    $tmp_file       : 서버로 업로드된 임시 파일 
//    $file_name_plus : 관리를 위해 파일앞에 붙여주는 문자열 
// 
//  리턴값 
//    파일저장에 성공햇을겨우 바꾸어진 파일 이름을 넘긴다. 
//    실패할경우 널문자를 넘긴다. 
//========================================================================================= 
function upload_file($upload_dir, $tmp_file, $file_name_plus="" ) 
{ 
        // 파일이름을 시간으로 바꿔서 php등으 스크립트 언어가 실행되지 못하게한다. 
        $new_file_name = microtime(); 


        // 파일이름이 겹친다면 (마이크로 타임으로 바꾸었기 때문에 이럴일은 거의 없다.) 
        // 파일 확장자 전에 '_i' 를  붙인다. (i는 임의의숫자) 
        $old_new_file_name = $new_file_name; 
        for( $i=0; file_exists( $upload_dir.$new_file_name ); $i++ ) 
                $new_file_name = $old_new_file_name."_".$i; 


        // 관리를 쉽게하기위해 file_name_plus를 앞에 달아준다. 
        $new_file_name = $file_name_plus.$new_file_name; 

        //빈칸을 없앤다. 
       $new_file_name = str_replace(" ","_", $new_file_name); 

        // 임시디렉토리에서 지정디렉토리로 파일을 옴긴다. 
        if( is_uploaded_file( $tmp_file ) ) 
                move_uploaded_file( $tmp_file, $upload_dir.$new_file_name ); 
        else return ""; 

        return $new_file_name; 
} 


//========================================================================================= 
//  이름 바꿔 다운로드하기 
// 
//  파라미터 
//    $file_name  : 실제파일이름 
//    $file_dir   : 파일의 위치 
//    $file_micro : 바뀐 파일 이름명 
//    $file_type  : 파일의 다운로드 방식, 비워두면 일반 파일이다. 
//                                    - 동영상  : video/mpeg, video/x-msvideo 
//                                    - 이미지  : image/jpeg, image/gif, image/png 
//                                    - Zip파일 : application/x-zip-compressed 
//                                    - txt파일 : text/plain 
//  리턴값 
//          완료했을 경우 0을 리턴한다. 
//          다운로드 파일이 없으면 요청시 1을 리턴한다. 
//          해킹시도등 잘못된 파일 요청시 2을 리턴한다. 
//========================================================================================= 
function download_file($file_name, $file_micro, $file_dir, $file_type ) 
{ 
        // 읽어올 파일명에 이상이있는지 검사한다. 
        if( !$file_name || !$file_micro || !$file_dir ) return 1; 
        if( eregi( "\\\\|\.\.|/", $file_micro ) ) return 2; 


        if( file_exists($file_dir.$file_micro) ) 
        { 
                $fp = fopen($file_dir.$file_micro,"r"); 

                if( $file_type ) 
                { 
                        header("Content-type: $file_type"); 
                        Header("Content-Length: ".filesize($file_dir.$file_micro));     
                        Header("Content-Disposition: attachment; filename=$file_name");   
                        Header("Content-Transfer-Encoding: binary"); 
                        header("Expires: 0"); 
                } 
                else 
                { 
                        if(eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $HTTP_USER_AGENT)) 
                        { 
                                Header("Content-type: application/octet-stream"); 
                                Header("Content-Length: ".filesize($file_dir.$file_micro));     
                                Header("Content-Disposition: attachment; filename=$file_name");   
                                Header("Content-Transfer-Encoding: binary");   
                                Header("Expires: 0");   
                        } 
                        else 
                        { 
                                Header("Content-type: file/unknown");     
                                Header("Content-Length: ".filesize($file_dir.$file_micro)); 
                                Header("Content-Disposition: attachment; filename=$file_name"); 
                                Header("Content-Descript-xion: PHP3 Generated Data"); 
                                Header("Expires: 0"); 
                        } 
                } 


                fpassthru($fp); 
                fclose($fp); 
        } 
        else return 1; 
} 

//========================================================================================= 
//  파일 삭제하기 
// 
//  파라미터 
//    $file_name  : 실제파일이름 
//    $file_dir   : 파일의 위치 
// 
//  리턴값 
//          완료했을 경우 0을 리턴한다. 
//          삭제할 파일이 없으면 요청시 1을 리턴한다. 
//          해킹시도등 잘못된 파일 요청시 2을 리턴한다. 
//    파일이 존재하지만 지울수 없는겨우 3을 리턴한다. 
//========================================================================================= 
function delete_file($file_name, $file_dir ) 
{ 
        // 읽어올 파일명에 이상이있는지 검사한다. 
        if( !$file_name || !$file_dir ) return 1; 
        if( eregi( "\\\\|\.\.|/", $file_name ) ) return 2; 

        // 파일이 있나 검사. 
        if( !file_exists($file_dir.$file_micro) ) return 1; 

        // 있는데 못지웠을 경우 
        if( !unlink() ) return 3; 

        return 0; 
} 
?>