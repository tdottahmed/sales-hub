<meta charset="utf-8" />
<title>{{ getSetting('app_name') . ' | ' . (request()->segments() ? Str::title(last(request()->segments())) : 'Welcome') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
<meta content="Themesbrand" name="author" />
<!-- App favicon -->
<link rel="shortcut icon" href="{{ getFilePath(getSetting('app_favicon')) }}">
<link href="/assets/admin/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

<!-- jsvectormap css -->
<link href="/assets/admin/libs/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css" />

<!--Swiper slider css-->
<link href="/assets/admin/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

<!-- Layout config Js -->
<script src="/assets/admin/js/layout.js"></script>
<!-- Bootstrap Css -->
<link href="/assets/admin/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="/assets/admin/css/icons.min.css" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="/assets/admin/css/app.min.css" rel="stylesheet" type="text/css" />
<!-- custom Css-->
<link href="/assets/admin/css/custom.min.css" rel="stylesheet" type="text/css" />
<link href="/assets/admin/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

@stack('styles')
