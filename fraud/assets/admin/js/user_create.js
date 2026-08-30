(function($) {
    "use strict";


        //schedule checkbox is check or not
        $(document).on('change',".schedule",function(){
            if($('.schedule').is(':checked')){
                $('.datepick').css('display','block');
                $('.saveAsDraft').css('display','none');
                // $('.addPost').css('margin-left','150px');
            }else{
                $('.datepick').css('display','none');
                $('.saveAsDraft').css('display','inline-block');
                $('#from').val('');
            }
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
    
        //get the category which belongs to a specific language
        $(document).on('change',"#article_language_id",function(){
            $('.categoryDiv').css('display','block');
            var x = $(this).val();
            var url = mainurl+'user/add-article/language/'+x;
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


        //get the subcategory which belongs to a specific category
        $(document).on('change',"#article_parent_id",function(){
            var x = $(this).val();
            var url = mainurl+'user/add-article/subcategory/'+x;
            $.ajax({
                type        : 'GET',
                url         : url,
                contentType : false,
                processData : false,
                data        : {},
                success     : function(data){
                            $("#subcategory_id").html(data);
                }
            })
        })

    
        //get the subcategory which belongs to a specific category
        $(document).on('change',"#article_parent_id",function(){
            var x = $(this).val();
            var id = $("#article_post_id").val();
            var url = mainurl+'user/edit-article/subcategoryUpdate/'+x+'/'+id;
            $.ajax({
                type        : 'GET',
                url         : url,
                contentType : false,
                processData : false,
                data        : {},
                success     : function(data){
                            $("#subcategory_id").html(data);
                }
            })
        })

        //Datepicker initiate 
        var dateToday = new Date();
        var dates =  $( "#from" ).datetimepicker({
            format:'Y-m-d H:i:s',
            minDate:dateToday,
        });

        $("#videoAdd").on('change',function(){
            var videoOption = $('option:selected',this).attr('value');
            if(videoOption == 'embed_video'){
                console.log(videoOption);
                $(".embedVideo").css('display','block');
                $(".localVideo").css('display','none');
            }else{
                $(".embedVideo").css('display','none');
                $(".localVideo").css('display','block');
            }
        })
    
    
        //video preview create
        $(function() {
            $('.upload-video-file').on('change', function(){
            if (isVideo($(this).val())){
                $('.video-preview').attr('src', URL.createObjectURL(this.files[0]));
                $('.video-prev').show();
            }
            else
            {
                $('.upload-video-file').val('');
                $('.video-prev').hide();
                alert("Only video files are allowed to upload.")
            }
            });
        });
        
        // If user tries to upload videos other than these extension , it will throw error.
        function isVideo(filename) {
            var ext = getExtension(filename);
            switch (ext.toLowerCase()) {
            case 'm4v':
            case 'avi':
            case 'mp4':
            case 'mov':
            case 'mpg':
            case 'mpeg':
            case 'webm':
                // etc
                return true;
            }
            return false;
        }
        
        function getExtension(filename) {
            var parts = filename.split('.');
            return parts[parts.length - 1];
        }

        $(document).on("click", ".set-gallery" , function(){
            var post_id = $('#post_id').val();
        
            $('#post_id').val(post_id);
            $('.selected-image .row').html('');
            var url = mainurl+'user/gallery/show';
        
                $.ajax({
                    type: "GET",
                    url:url,
                    data:{id:post_id},
                    success:function(data){
                        if(data[0] == 0)
                        {
                        $('.selected-image .row').addClass('justify-content-center');
                            $('.selected-image .row').html('<h3>No Images Found.</h3>');
                        }
                        else {
                        $('.selected-image .row').removeClass('justify-content-center');
                            $('.selected-image .row h3').remove();    
                            var arr = $.map(data[1], function(el) {
                            return el });
    
                            for(var k in arr)
                            {
                        $('.selected-image .row').append('<div class="col-sm-6">'+
                                        '<div class="img gallery-img">'+
                                            '<span class="remove-img"><i class="fas fa-times"></i>'+
                                            '<input type="hidden" value="'+arr[k]['id']+'">'+
                                            '</span>'+
                                            '<a href="'+mainurl+'assets/images/galleries/'+arr[k]['photo']+'" target="_blank">'+
                                            '<img src="'+mainurl+'assets/images/galleries/'+arr[k]['photo']+'" alt="gallery image">'+
                                            '</a>'+
                                        '</div>'+
                                        '</div>');
                            }                         
                        }
    
                    }
                });
          });
        
        $(document).on('click', '.remove-img' ,function() {
            var id = $(this).find('input[type=hidden]').val();
            $(this).parent().parent().remove();
            var url = mainurl+'user/gallery/delete';
            $.ajax({
                type: "GET",
                url:url,
                data:{id:id},
                success: function(data){
                    console.log(data);
                }
            });
        });
        
        $(document).on('click', '#article_gallery_edit' ,function() {
        $('#article_upload_gallery_edit').click();
        });
                                            
                                    
        $("#article_upload_gallery_edit").change(function(){
        $("#form-gallery").submit();  
        });
        
        
        $(document).on('submit', '#form-gallery' ,function() {
            var url = mainurl+'user/gallery/store';
              $.ajax({
               url:url,
               method:"POST",
               data:new FormData(this),
               dataType:'JSON',
               contentType: false,
               cache: false,
               processData: false,
               success:function(data)
               {
                if(data != 0)
                {
                    $('.selected-image .row').removeClass('justify-content-center');
                      $('.selected-image .row h3').remove();   
                    var arr = $.map(data, function(el) {
                    return el });
                    for(var k in arr)
                       {
                            $('.selected-image .row').append('<div class="col-sm-6">'+
                                            '<div class="img gallery-img">'+
                                                '<span class="remove-img"><i class="fas fa-times"></i>'+
                                                '<input type="hidden" value="'+arr[k]['id']+'">'+
                                                '</span>'+
                                                '<a href="'+mainurl+'assets/images/galleries/'+arr[k]['photo']+'" target="_blank">'+
                                                '<img src="'+mainurl+'assets/images/galleries/'+arr[k]['photo']+'" alt="gallery image">'+
                                                '</a>'+
                                            '</div>'+
                                          '</div>');
                        }          
                }
                                 
                                   }
        
              });
              return false;
        }); 

        $(document).on('click', '.remove-img', function () {
            var id = $(this).find('input[type=hidden]').val();
            $('#galval' + id).remove();
            $(this).parent().parent().remove();
        });

        $(document).on('click', '#article_gallery', function () {
            $('#articleuploadgallery').click();
            // $('.selected-image .row').html('');
            // $('#geniusformdata2').find('.removegal').val(0);
        });


        $("#articleuploadgallery").change(function (event) {
            var total_file = document.getElementById("articleuploadgallery").files.length;
            for (var i = 0; i < total_file; i++) {
                $('.selected-image .row').append('<div class="col-sm-6">' +
                    '<div class="img gallery-img">' +
                    '<span class="remove-img"><i class="fas fa-times"></i>' +
                    '<input type="hidden" value="' + i + '">' +
                    '</span>' +
                    '<a href="' + URL.createObjectURL(event.target.files[i]) + '" target="_blank">' +
                    '<img src="' + URL.createObjectURL(event.target.files[i]) + '" alt="gallery image">' +
                    '</a>' +
                    '</div>' +
                    '</div> '
                );
                $('#geniusformdata2').append('<input type="hidden" name="gallery[]" id="galval' + i +
                    '" class="removegal" value="' + i + '">')
            }

        });

})(jQuery);