function resetTabs(){
        jQuery("#content > div").hide(); //Hide all content
        jQuery("#tabs a").attr("id",""); //Reset id's      
    }

    var myUrl = window.location.href; //get URL
    var myUrlTab = myUrl.substring(myUrl.indexOf("#")); // For localhost/tabs.html#tab2, myUrlTab = #tab2     
    var myUrlTabName = myUrlTab.substring(0,4); // For the above example, myUrlTabName = #tab

    (function(){
        jQuery("#content > div").hide(); // Initially hide all content
        jQuery("#tabs li:first a").attr("id","current"); // Activate first tab
        jQuery("#content > div:first").fadeIn(); // Show first tab content
        
        jQuery("#tabs a").on("click",function(e) {
            e.preventDefault();
            if (jQuery(this).attr("id") == "current"){ //detection for current tab
             return       
            }
            else{             
            resetTabs();
            jQuery(this).attr("id","current"); // Activate this
            jQuery(jQuery(this).attr('name')).fadeIn(); // Show content for current tab
            }
        });

        for (i = 1; i <= jQuery("#tabs li").length; i++) {
          if (myUrlTab == myUrlTabName + i) {
              resetTabs();
              jQuery("a[name='"+myUrlTab+"']").attr("id","current"); // Activate url tab
              jQuery(myUrlTab).fadeIn(); // Show url tab content        
          }
        }
    })()


