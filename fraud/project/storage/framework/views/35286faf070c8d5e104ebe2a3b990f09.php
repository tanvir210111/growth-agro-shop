<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo $__env->yieldContent('meta'); ?>
    <title><?php echo e($gs->title); ?></title>
    <meta name="google-site-verification" content="RmBXIivmPeG5ymjxBj8ZiDEq399ypy5So2_XxKyiRtQ" />
    <link rel="shortcut icon" href="<?php echo e(asset('assets/images/'.$gs->favicon)); ?>" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('assets/front/css/site.css')); ?>?v=10">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="theme-bdcourier">
    <?php echo $__env->make('partial.front.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->yieldContent('contents'); ?>

    <?php echo $__env->make('partial.front.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script>
    function changeLanguage(lang) {
        if (lang === 'bn') {
            try { localStorage.setItem('siteLang', 'bn'); } catch (e) {}
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=." + document.domain + "; path=/;";
        } else {
            try { localStorage.setItem('siteLang', 'en'); } catch (e) {}
            document.cookie = "googtrans=/bn/en; path=/";
        }
        location.reload();
    }

    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'bn',
            includedLanguages: 'bn,en',
            autoDisplay: false
        }, 'google_translate_element');

        setTimeout(function () {
            var googleBar = document.querySelector('.goog-te-banner-frame');
            if (googleBar) googleBar.style.display = 'none';
            document.body.style.top = '0';
        }, 100);
    }

    window.addEventListener('load', function () {
        setInterval(function () {
            document.querySelectorAll('iframe.goog-te-banner-frame, .skiptranslate iframe').forEach(function (frame) {
                frame.style.display = 'none';
                frame.style.visibility = 'hidden';
            });
            if (document.body.style.top !== '0px') document.body.style.top = '0px';
        }, 500);
    });
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo e(asset('assets/front/js/site.js')); ?>?v=6"></script>

    <script>
    $(function () {
        if ($('.brand-slider').length) {
            $('.brand-slider').owlCarousel({
                loop: true, margin: 30, nav: false, dots: false,
                autoplay: true, autoplayTimeout: 3000, autoplayHoverPause: true,
                responsive: { 0: { items: 2 }, 600: { items: 3 }, 1000: { items: 6 } }
            });
        }
        if ($('.testimonial-carousel').length) {
            $('.testimonial-carousel').owlCarousel({
                loop: true, margin: 20, nav: false, dots: true,
                autoplay: true, autoplayTimeout: 4000, smartSpeed: 1000,
                responsive: { 0: { items: 1 }, 768: { items: 2 }, 1000: { items: 3 } }
            });
        }
        if ($('.team-slider').length) {
            $('.team-slider').owlCarousel({
                loop: true, margin: 20, nav: false, dots: true,
                autoplay: true, autoplayTimeout: 4000,
                responsive: { 0: { items: 1 }, 600: { items: 2 }, 1000: { items: 4 } }
            });
        }
        if ($('.portfolio-grid').length) {
            var $grid = $('.portfolio-grid').isotope({
                itemSelector: '.portfolio-item',
                layoutMode: 'fitRows',
                percentPosition: true
            });
            $('.portfolio-menu').on('click', 'button', function () {
                var filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
                $(this).addClass('active').siblings('.active').removeClass('active');
            });
        }
    });
    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/layouts/front.blade.php ENDPATH**/ ?>