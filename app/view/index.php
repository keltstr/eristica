<!doctype html>
    <!--[if lte IE 8]><html class="lteie8"><![endif]-->
    <!--[if gt IE 8]><!--><html><!--<![endif]-->
    <head>
        <title>Playtop TV - <?php iv('title')?></title>
        <link rel="icon" href="/favicon.ico">
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
        <!--<meta name="viewport" content="initial-scale=1"/>-->
    </head>

    <body>
        <header class="wrapper">
            <div class="spoiler">
                We are sorry for inconveniences! We are making design now!
            </div>
            <a class="logo" href="/">
                <img src="/images/logo.png" alt="Playtop TV logo">
                <span class="slogan">PLAY ONLY BEST</span>
            </a>
            <div class="controls">
                <?php m('i18n')->render('list')?>
                <?php m('user')->render('panel')?>
            </div>
        </header>

        <section class="video wrapper">
            <div class="player" id="player"></div>
        </section>

        <section class="categories wrapper clearfix">
            <div class="arrow left disabled"></div>
            <div class="arrow right"></div>
            <div class="container">
                <ul>
                    <li>
                        <a href="#billboard" class="active" category="1">
                            <span class="top">top 100</span>
                            <span class="text">billboard</span>
                            <span class="glance"></span>
                        </a>
                    </li>
                    <li>
                        <a href="#music" category="4">
                            <span class="top">top</span>
                            <span class="text">music</span>
                            <span class="glance"></span>
                        </a>
                    </li>
                    <li>
                        <a href="#news" category="8">
                            <span class="top">top</span>
                            <span class="text">news</span>
                            <span class="glance"></span>
                        </a>
                    </li>
                    <li>
                        <a href="#comedy" category="2">
                            <span class="top">top</span>
                            <span class="text">comedy</span>
                            <span class="glance"></span>
                        </a>
                    </li>
                    <li>
                        <a href="#k-pop" category="5">
                            <span class="top">top</span>
                            <span class="text">k-pop</span>
                            <span class="glance"></span>
                        </a>
                    </li>
                </ul>
            </div>
        </section>

        <footer>

        </footer>

        <?php echo m()->output('user/auth-popup')?>

        <!-- Load first video data from server-->
        <script>var firstVideo = <?php iv('videoData')?>;</script>
	<body>
</html>  