$(function(){

    //フッター固定
    let $footer = $('#footer');
        if(window.innerHeight > $footer.offset().top + $footer.outerHeight() ) {
            $footer.attr({'style': 'position:fixed; top:' + (window.innerHeight - $footer.outerHeight()) + 'px;' });
        }

    //画像ライブプレビュー
    let $dropIcon = $('.dropIcon-area');
    let $imgFile = $('.img-file');

    $dropIcon.on('dragover', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '2px dashed #707070');
    })

    $dropIcon.on('dragleave', function(e){
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', 'none');
    })

    $imgFile.on('change', function(){
        $dropIcon.css('border', 'none');
        let file = this.files[0];
        let $img = $(this).siblings($(this).find('img'));
        let fileReader = new FileReader();

        fileReader.onload = function(e){
            $img.attr('src', e.target.result).show();
        }
        fileReader.readAsDataURL(file);
    })


    //画像切替
    let $mainImg = $('.js-mainImg');
    let $subImg = $('.js-subImg');

    $subImg.on('click', function(){
        subImgSrc = $(this).attr('src');
        $mainImg.attr('src',subImgSrc);
    })

    //スライドメッセージ表示
    let $slideMsg = $('#js-slideMsg');
    let msg = $slideMsg.text();
    if(msg.replace(/\s+/g, "")){
        $slideMsg.addClass('show').fadeIn();
        // $slideMsg.slideToggle().show();
        setTimeout(function(){
            $slideMsg.fadeOut();
        }, 5000);
    }

    //コメントのテキストカウント
    let $commentArea = $('#js-comment');
    let $textCount = $('#js-textCount');
    let showCount = $textCount.find('span');

    $commentArea.on('keyup', function(){
        showCount.text($(this).val().length);
    })

})