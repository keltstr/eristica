/**
 * Created by Vitaly Egorov <egorov@samsonos.com> on 07.04.14.
 */
s('.invite').pageInit(function(p){
    // Handle form submit event
    s('form', p).submit(function(form){

        // Hide form fields
        s('.input').hide();
        s('.complete').fadeIn();

        // Show thanks message and hide it in 30 sec
        setTimeout(function(){
            s('.complete').fadeOut('slow',function(){
                s('input[type=text],input[type=email], textarea').val('');
                s('.input').show();
            });
        },2000);

        // Send form asynchronously
        form.ajaxForm();

        return false;
    });
});

s('.support').pageInit(function(btn){
    btn.click(function(){

        s('.container.subscribe').toggle();
        s('.container.question').toggle();

       return false;
    });
});