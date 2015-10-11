<?php

use PhangoApp\PhaView\View;
use PhangoApp\PhaI18n\I18n;

function ProgressView($url_to_progress, $title, $category, $module, $script)
{

    ob_start();
    
    ?>
     <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <link rel="stylesheet" href="/resources/demos/style.css">
     <style>
        .ui-progressbar {
        position: relative;
        }
        .progress-label {
        position: absolute;
        left: 50%;
        top: 4px;
        font-weight: bold;
        text-shadow: 1px 1px 0 #fff;
        }
    </style>
    <script language="javascript">
        $(document).ready(function () {
        
            var progressbar = $( "#progressbar" ),
            progressLabel = $( ".progress-label" );
            final_txt="<?php echo I18n::lang('theserver', 'complete', 'Complete!'); ?>";
            error_txt='';
            
            progressbar.progressbar({
                value: false,
                change: function() {
                    progressLabel.text( progressbar.progressbar( "value" ) + "%" );
                },
                complete: function() {
                
                    progressLabel.text( final_txt );
                }
            });
            
            function progress() {
            
                $.ajax({
                
                    url: "<?php echo $url_to_progress; ?>",
                    dataType: "json"
                    
                }).done(function(data) {
                
                    my_progress=parseInt(data.PROGRESS);
                
                    if(my_progress<100)
                    {
                        
                        //val = progressbar.progressbar( "value" ) || 0;
                        val=my_progress;
                        
                        progressbar.progressbar( "value", val);
                        
                        final_txt=data.MESSAGE;
                        
                        setTimeout( progress, 1000 );
                    }
                    else
                    {
                    
                        error=parseInt(data.ERROR, 10);
                        
                        val=100;
                        
                        if(error!=0)
                        {
                            
                            error_txt=data.MESSAGE;
                            
                            $('#error_message').text(error_txt);
                        
                        }
                        else
                        {
                        
                            final_txt=data.MESSAGE;
                        
                        }
                        
                        progressbar.progressbar( "value", val);
                    
                    }
                    
                    
                }).fail ( function(data) {
                
                    alert(data);
                
                });
            
                /*
                val = progressbar.progressbar( "value" ) || 0;
                
                progressbar.progressbar( "value", val + 2 );

                if ( val < 99 ) 
                {
                    setTimeout( progress, 80 );
                }*/
            }
            
            //setTimeout( progress, 1000 );
            
            progress();

        
        });
    </script>
    <?php
    View::$header[]=ob_get_contents();
    
    ob_end_clean();
    
    ?>
    <div class="title">
        <?php echo $title; ?>
    </div>
    <div class="cont">
        <div span class="error" id="error_message">
        </div>
        <div id="progressbar">
            <div class="progress-label"><?php echo I18n::lang('pastafari', 'loading', 'Loading...'); ?></div>
        </div>
    </div>
    <?php
    
}

?>
