<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 01.04.14 at 18:34
 */
?>
<section class="invite">    
    <form class="container subscribe" action="<?php url_base('parse','subscribe')?>" method="POST" target="_blank">
        <a class="logo">
            <img src="/images/logo.png">
        </a>
        <div class="input">
            <h1 class="spoiler">Эристика это новая социальная сеть для споров с друзьями</h1>
            <input required type="text" class="name" name="FNAME" placeholder="Имя">
            <input required type="email" class="email" name="EMAIL" placeholder="E-mail">
            <input type="submit" class="submit" value="Подписаться">
        </div>
        <div class="complete" style="display:none;">Красавчик!</div>
        <div class="social">
            <a href="http://instagram.com/Eristica/" class="in"></a>
            <a href="http://vk.com/eristica" class="vk"></a>
            <a href="facebook.com/eristica" class="fb"></a>
            <a href="https://twitter.com/eristicaapp" class="tw"></a>
        </div>
    </form>
    <form class="container question" style="display:none" action="<?php url_base('parse','question')?>" method="post">
        <a class="logo">
            <img src="/images/logo.png">
        </a>
        <div class="input">
            <textarea required name="QUESTION" placeholder="Напиши любой вопрос, комментарий, пожелание, крутой спор или анекдот - мы с радостью ответим"></textarea>
            <input required type="email" class="email" name="EMAIL" placeholder="E-mail">
            <input type="submit" class="submit" value="Отправить">
        </div>
        <div class="complete" style="display:none;">Красавчик!</div>
    </form>
</section>
<section class="mobile">
    <div class="comming-soon">Очень скоро</div>
    <a class="appstore"><img src="/images/appstore.png"></a>
    <a class="googleplay"><img src="/images/googleplay.png"></a>
</section>
<a style="display:none" class="info" href="">i</a>
<a style="" class="support">Вопросы?</a>




