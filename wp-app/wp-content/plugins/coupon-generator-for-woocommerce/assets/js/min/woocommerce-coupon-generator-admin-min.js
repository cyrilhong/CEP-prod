var WCCG_Generator={init:function(){this.generate_coupons(0,this)},generate_coupons:function(e,r){var o={action:"wccg_generate_coupons",form_data:jQuery("#wc-coupon-generator-form").serialize(),batch_step:e};jQuery.post(ajaxurl,o,function(e){e=JSON.parse(e),e.message&&r.add_message(e.message),"done"==e.step?r.completed(r,e.total_coupons_generated,e.total_coupons_generated):r.generate_coupons(e.step,r),r.progress_bar(e.progress)})},progress_bar:function(e){jQuery(".wc-coupon-generator-progress-bar .progress").css("width",e+"%"),jQuery(".wc-coupon-generator-progress-percentage").html(e+"%"),jQuery(".inner-progress").css("width",jQuery(".wc-coupon-generator-progress-bar").width())},completed:function(e,r,o){jQuery(".wc-coupon-generator-progress-bar + .spinner").remove()},add_message:function(e){jQuery(".wc-coupon-generator-progress-messages").prepend('<span class="wc-coupon-generator-progress-message">'+e+"</span><br/>")}};