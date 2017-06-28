jQuery(function ($) {

    $("#answer").summernote({

        lang:'ru-Ru',
        height:200,
        minwidth:400,
        minHeight:200,
        maxHeight:400,
        focus:false,
        placeholder:'Введите ваш комментарий',
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
        toolbar:[
            ['insert', ['link']],
            ['style', ['bold', 'italic', 'underline']],
            ['fontsize', ['fontsize']],
            ['fontname', ['fontname']],
            ['color', ['color']]
        ],
        disableDragAndDrop:true
    });

});

