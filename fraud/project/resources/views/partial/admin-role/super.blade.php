
{{-- --- অর্থ ব্যবস্থাপনা এবং পস সেকশন শুরু --- --}}
<li>
    <a href="#finance-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-wallet"></i>{{ __('Finance & POS') }}
    </a>
    <ul class="collapse list-unstyled" id="finance-section" data-parent="#accordion">
        <li>
            {{-- পস সেল পেজ (GET রাউট ব্যবহার করা হয়েছে) --}}
            <a href="{{ route('admin.pos.index') }}"><span><i class="fas fa-shopping-cart"></i> {{ __('POS - New Sale') }}</span></a>
        </li>
        <li>
            {{-- কাস্টমার লিস্ট --}}
            <a href="{{ route('admin.customer.index') }}"><span><i class="fas fa-user-friends"></i> {{ __('Customers') }}</span></a>
        </li>
        <li>
            {{-- দৈনিক খরচ এন্ট্রি --}}
            <a href="{{ route('admin.expense.index') }}"><span><i class="fas fa-money-bill-wave"></i> {{ __('Daily Expenses') }}</span></a>
        </li>
        <li>
            {{-- তহবিল এবং আয়-ব্যয় রিপোর্ট --}}
            <a href="{{ route('admin.report.fund') }}"><span><i class="fas fa-chart-line"></i> {{ __('Fund Statement') }}</span></a>
        </li>
    </ul>
</li>
{{-- --- অর্থ ব্যবস্থাপনা এবং পস সেকশন শেষ --- --}}

<li>
    <a href="#product-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-shopping-cart"></i>{{ __('Theme Management') }}
    </a>
    <ul class="collapse list-unstyled" id="product-section" data-parent="#accordion">
        <li>
            {{-- সকল থিমের তালিকা দেখার লিঙ্ক --}}
            <a href="{{ route('admin.products.index') }}"><span>{{ __('All Themes') }}</span></a>
        </li>
        <li>
            {{-- নতুন থিম যোগ করার লিঙ্ক --}}
            <a href="{{ route('admin.products.create') }}"><span>{{ __('Add New Theme') }}</span></a>
        </li>
    </ul>
</li>

{{-- --- Fraud Checker Section --- --}}
<li>
    <a href="#fraud-checker-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-shield-alt"></i>{{ __('Fraud Checker') }}
    </a>
    <ul class="collapse list-unstyled" id="fraud-checker-section" data-parent="#accordion">
        <li>
            <a href="{{ route('admin.fraud.index') }}">
                <span><i class="fas fa-search"></i> {{ __('Check Customer') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.fraud.logs') }}">
                <span><i class="fas fa-history"></i> {{ __('Check History') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.fraud.couriers') }}">
                <span><i class="fas fa-user-lock"></i> {{ __('Courier Accounts') }}</span>
            </a>
        </li>
    </ul>
</li>
{{-- --- Fraud Checker Section End --- --}}



<li>
    <a href="#service-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-tools"></i>{{ __('Our Services') }}
    </a>
    <ul class="collapse list-unstyled" id="service-section" data-parent="#accordion">
        <li>
            <a href="{{ route('admin.service.index') }}"><span>{{ __('All Services') }}</span></a>
        </li>
        <li>
            <a href="{{ route('admin.service.create') }}"><span>{{ __('Add New Service') }}</span></a>
        </li>
    </ul>
</li>

<li>
    <a href="#portfolio-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-briefcase"></i>{{ __('Portfolio') }}
    </a>
    <ul class="collapse list-unstyled" id="portfolio-section" data-parent="#accordion">
        <li>
            <a href="{{ route('admin.portfolio-category.index') }}"><span>{{ __('Categories') }}</span></a>
        </li>
        <li>
            <a href="{{ route('admin.portfolio.index') }}"><span>{{ __('All Projects') }}</span></a>
        </li>
        <li>
            <a href="{{ route('admin.portfolio.create') }}"><span>{{ __('Add New Project') }}</span></a>
        </li>
    </ul>
</li>







<li>
    <a href="#menu-team" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-users"></i>{{ __('Team Management') }}
    </a>
    <ul class="collapse list-unstyled" id="menu-team" data-parent="#accordion">
        <li>
            <a href="{{ route('admin.team.index') }}"><span>{{ __('All Members') }}</span></a>
        </li>
        <li>
            <a href="{{ route('admin.team.create') }}"><span>{{ __('Add New Member') }}</span></a>
        </li>
    </ul>
</li>

{{-- --- সাপোর্ট টিকেট সেকশন শুরু --- --}}
<li>
    <a href="{{ route('admin.tickets.index') }}" class="wave-effect">
        <i class="fas fa-headset"></i> 
        <span>{{ __('Support Tickets') }}</span>
        
        @php
            // পেন্ডিং টিকেটের সংখ্যা গণনার জন্য (আপনার ডাটাবেস অনুযায়ী)
            $pending_tickets_count = \Illuminate\Support\Facades\DB::table('tickets')->where('status', 'pending')->count();
        @endphp
        
        @if($pending_tickets_count > 0)
            <span class="badge badge-danger ml-2">{{ $pending_tickets_count }}</span>
        @endif
    </a>
</li>
{{-- --- সাপোর্ট টিকেট সেকশন শেষ --- --}}


<li>
    <a href="{{ route('admin.consultancy.index') }}" class="wave-effect">
        <i class="fas fa-headset"></i> <span>{{ __('Free Consultancy') }}</span>
    </a>
</li>

<li>
    <a href="{{ route('admin.contact.index') }}" class="wave-effect">
        <i class="fas fa-envelope"></i> <span>{{ __('Contact Messages') }}</span>
    </a>
</li>

{{-- --- User/Staff Management Menu Start --- --}}
<li>
    <a href="#menu-users" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-users"></i>{{ __('User Management') }}
    </a>
    <ul class="collapse list-unstyled" id="menu-users" data-parent="#accordion">
        
        {{-- ১. নতুন ইউজার তৈরি --}}
        <li>
            <a href="{{ route('admin.staff.create') }}">
                <span><i class="fas fa-user-plus"></i> {{ __('Add New User') }}</span>
            </a>
        </li>

        {{-- ২. সকল ইউজার লিস্ট --}}
        <li>
            <a href="{{ route('admin.staff.index') }}">
                <span><i class="fas fa-list-ul"></i> {{ __('All Users List') }}</span>
            </a>
        </li>

    </ul>
</li>
{{-- --- User/Staff Management Menu End --- --}}






<li>
    <a href="#frontend-customization" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-desktop"></i>{{ __('Frontend Customization') }}
    </a>
    <ul class="collapse list-unstyled" id="frontend-customization" data-parent="#accordion">






        <li>
            <a href="{{ route('admin.about.edit') }}"><span>{{ __('Edit About Section') }}</span></a>
        </li>


        <li>
            <a href="{{ route('admin.testimonial.index') }}"><span>{{ __('Testimonials') }}</span></a>
        </li>

        <li>
            <a href="{{ route('admin.counter.index') }}"><span>{{ __('Counter Section') }}</span></a>
        </li>
<li>
            <a href="{{ route('admin.service.edit_text') }}"><span>{{ __('Edit Section Text') }}</span></a>
        </li>

<li>
            <a href="{{ route('admin.brand.index') }}"><span>{{ __('All Brands') }}</span></a>
        </li>
   
<li>
            <a href="{{ route('admin.pricing.index') }}"><span>{{ __('All Pricing Plans') }}</span></a>
        </li>
<li>
            <a href="{{ route('admin.why-choose-us.index') }}"><span>{{ __('Why Choose Us - All') }}</span></a>
        </li>
		
	<li>
            <a href="{{ route('social.link.index') }}"><span>{{ __('Social Settings') }}</span></a>
        </li>	
		

    </ul>
</li>


<li>
    <a href="#menu10" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fa fa-language"></i>{{ __('Languages') }}
    </a>
    <ul class="collapse list-unstyled" id="menu10" data-parent="#accordion">
        <li>
            <a href="{{ route('admin.language.index') }}"><span><i class="fas fa-angle-double-right"></i>{{ __('Language') }}</span></a>
        </li>

        <li>
            <a href="{{ route('admin.admin_language.index') }}"><span><i class="fas fa-angle-double-right"></i>{{ __('Admin Language') }}</span></a>
        </li>
    </ul>
</li>



  
<li>
    <a href="#general" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-cogs"></i>{{__('General Settings')}}
    </a>
	

	
    <ul class="collapse list-unstyled" id="general" data-parent="#accordion">
	
	
		        <li>
            <a href="{{route('admin.generalsettings.websiteContent')}}"><span><i class="fas fa-angle-double-right"></i>{{__('Website settings')}}</span></a>
        </li>
        <li>
            <a href="{{route('admin.generalsettings.logo')}}"><span><i class="fas fa-angle-double-right"></i>{{__('Logo')}}</span></a>
        </li>
        <li>
            <a href="{{route('admin.languagelogo.index')}}"><span><i class="fas fa-angle-double-right"></i>{{__('Language Base Logo')}}</span></a>
        </li>
        <li>
            <a href="{{route('admin.generalsettings.favicon')}}"><span><i class="fas fa-angle-double-right"></i>{{__('Favicon')}}</span></a>
        </li>





    </ul>
</li>




   


  
<li>
    <a href="#emails" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-at"></i>{{__('Email Settings')}}
    </a>
    <ul class="collapse list-unstyled" id="emails" data-parent="#accordion">
        <li><a href="{{route('admin.email.config')}}"><span><i class="fas fa-angle-double-right"></i>{{__('Email Configurations')}}</span></a></li>  
    </ul>
</li>


<li>
    <a href="#seoTools" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-wrench"></i>{{__('SEO Tools')}}
    </a>
    <ul class="collapse list-unstyled" id="seoTools" data-parent="#accordion">
        <li>
            <a href="{{ route('seo.meta.keywords') }}"><span><i class="fas fa-angle-double-right"></i>{{__('Website Meta Keywords')}}</span></a>
        </li>
    </ul>
</li>







<li>
    <a href="{{ route('admin.cache.clear') }}" class=" wave-effect"><i class="fa fa-database"></i>{{ __('Clear Cache') }}</a>
</li>    