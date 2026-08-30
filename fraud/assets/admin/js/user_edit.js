(function($) {
    "use strict";

        $(document).ready(function(){
            categoryByLanguage();
            var is_schedule = $('.schedule').val();
            if(is_schedule ==1){
                $('.datepick').css('display','block');
            }
            setTimeout(function(){ 
                category();
            }, 500);
        })

        $("#title").on('keyup',function(){
            var title = $(this).val();
            var url = mainurl+'user/add-article/slugCreate';
            $.ajax({
                type        : 'GET',
                url         : url,
                data        : {title:title},
                success     : function(data){
                                $("#slug").prop('value',data);
                }
            })
        })

        function categoryByLanguage(){
            $('.categoryDiv').css('display','block');
            var x = $("#article_language_id").val();
            var id = $("#article_post_id").val();

            var url = mainurl+'user/add-article/languageOnUpdate/'+x+'/'+id;
    
            $.ajax({
                type        : 'GET',
                url         : url,
                contentType : false,
                processData : false,
                data        : {},
                success     : function(data){
                                $("#article_parent_id").html(data);
                }
            })
        }
    

        $(document).on('change',"#article_language_id",function(){
            $('.categoryDiv').css('display','block');
            var x = $(this).val();
            var id = $("#article_post_id").val();
            console.log(x,id);

            var url = mainurl+'user/add-article/languageOnUpdate/'+x+'/'+id;
            $.ajax({
                type        : 'GET',
                url         : url,
                contentType : false,
                processData : false,
                data        : {},
                success     : function(data){
                                $("#article_parent_id").html(data);
                }
            });
        })
        
    
        function category(){
            var x = $("#article_parent_id").val();
            var id = $("#article_post_id").val();
            var url = mainurl+'user/edit-article/subcategoryUpdate/'+x+'/'+id;
            $.ajax({
                type        : 'GET',
                url         : url,
                contentType : false,
                processData : false,
                data        : {},
                success     : function(data){
                            // console.log(data);
                            $("#subcategory_id").html(data);
                }
            })
        }

})(jQuery);