

<li>
    <a href="#finance-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-wallet"></i><?php echo e(__('Finance & POS')); ?>

    </a>
    <ul class="collapse list-unstyled" id="finance-section" data-parent="#accordion">
        <li>
            
            <a href="<?php echo e(route('admin.pos.index')); ?>"><span><i class="fas fa-shopping-cart"></i> <?php echo e(__('POS - New Sale')); ?></span></a>
        </li>
        <li>
            
            <a href="<?php echo e(route('admin.customer.index')); ?>"><span><i class="fas fa-user-friends"></i> <?php echo e(__('Customers')); ?></span></a>
        </li>
        <li>
            
            <a href="<?php echo e(route('admin.expense.index')); ?>"><span><i class="fas fa-money-bill-wave"></i> <?php echo e(__('Daily Expenses')); ?></span></a>
        </li>
        <li>
            
            <a href="<?php echo e(route('admin.report.fund')); ?>"><span><i class="fas fa-chart-line"></i> <?php echo e(__('Fund Statement')); ?></span></a>
        </li>
    </ul>
</li>


<li>
    <a href="#product-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-shopping-cart"></i><?php echo e(__('Theme Management')); ?>

    </a>
    <ul class="collapse list-unstyled" id="product-section" data-parent="#accordion">
        <li>
            
            <a href="<?php echo e(route('admin.products.index')); ?>"><span><?php echo e(__('All Themes')); ?></span></a>
        </li>
        <li>
            
            <a href="<?php echo e(route('admin.products.create')); ?>"><span><?php echo e(__('Add New Theme')); ?></span></a>
        </li>
    </ul>
</li>


<li>
    <a href="#fraud-checker-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-shield-alt"></i><?php echo e(__('Fraud Checker')); ?>

    </a>
    <ul class="collapse list-unstyled" id="fraud-checker-section" data-parent="#accordion">
        <li>
            <a href="<?php echo e(route('admin.fraud.index')); ?>">
                <span><i class="fas fa-search"></i> <?php echo e(__('Check Customer')); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.fraud.logs')); ?>">
                <span><i class="fas fa-history"></i> <?php echo e(__('Check History')); ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.fraud.couriers')); ?>">
                <span><i class="fas fa-user-lock"></i> <?php echo e(__('Courier Accounts')); ?></span>
            </a>
        </li>
    </ul>
</li>




<li>
    <a href="#service-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-tools"></i><?php echo e(__('Our Services')); ?>

    </a>
    <ul class="collapse list-unstyled" id="service-section" data-parent="#accordion">
        <li>
            <a href="<?php echo e(route('admin.service.index')); ?>"><span><?php echo e(__('All Services')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.service.create')); ?>"><span><?php echo e(__('Add New Service')); ?></span></a>
        </li>
    </ul>
</li>

<li>
    <a href="#portfolio-section" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-briefcase"></i><?php echo e(__('Portfolio')); ?>

    </a>
    <ul class="collapse list-unstyled" id="portfolio-section" data-parent="#accordion">
        <li>
            <a href="<?php echo e(route('admin.portfolio-category.index')); ?>"><span><?php echo e(__('Categories')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.portfolio.index')); ?>"><span><?php echo e(__('All Projects')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.portfolio.create')); ?>"><span><?php echo e(__('Add New Project')); ?></span></a>
        </li>
    </ul>
</li>







<li>
    <a href="#menu-team" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-users"></i><?php echo e(__('Team Management')); ?>

    </a>
    <ul class="collapse list-unstyled" id="menu-team" data-parent="#accordion">
        <li>
            <a href="<?php echo e(route('admin.team.index')); ?>"><span><?php echo e(__('All Members')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.team.create')); ?>"><span><?php echo e(__('Add New Member')); ?></span></a>
        </li>
    </ul>
</li>


<li>
    <a href="<?php echo e(route('admin.tickets.index')); ?>" class="wave-effect">
        <i class="fas fa-headset"></i> 
        <span><?php echo e(__('Support Tickets')); ?></span>
        
        <?php
            // পেন্ডিং টিকেটের সংখ্যা গণনার জন্য (আপনার ডাটাবেস অনুযায়ী)
            $pending_tickets_count = \Illuminate\Support\Facades\DB::table('tickets')->where('status', 'pending')->count();
        ?>
        
        <?php if($pending_tickets_count > 0): ?>
            <span class="badge badge-danger ml-2"><?php echo e($pending_tickets_count); ?></span>
        <?php endif; ?>
    </a>
</li>



<li>
    <a href="<?php echo e(route('admin.consultancy.index')); ?>" class="wave-effect">
        <i class="fas fa-headset"></i> <span><?php echo e(__('Free Consultancy')); ?></span>
    </a>
</li>

<li>
    <a href="<?php echo e(route('admin.contact.index')); ?>" class="wave-effect">
        <i class="fas fa-envelope"></i> <span><?php echo e(__('Contact Messages')); ?></span>
    </a>
</li>


<li>
    <a href="#menu-users" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-users"></i><?php echo e(__('User Management')); ?>

    </a>
    <ul class="collapse list-unstyled" id="menu-users" data-parent="#accordion">
        
        
        <li>
            <a href="<?php echo e(route('admin.staff.create')); ?>">
                <span><i class="fas fa-user-plus"></i> <?php echo e(__('Add New User')); ?></span>
            </a>
        </li>

        
        <li>
            <a href="<?php echo e(route('admin.staff.index')); ?>">
                <span><i class="fas fa-list-ul"></i> <?php echo e(__('All Users List')); ?></span>
            </a>
        </li>

    </ul>
</li>







<li>
    <a href="#frontend-customization" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-desktop"></i><?php echo e(__('Frontend Customization')); ?>

    </a>
    <ul class="collapse list-unstyled" id="frontend-customization" data-parent="#accordion">






        <li>
            <a href="<?php echo e(route('admin.about.edit')); ?>"><span><?php echo e(__('Edit About Section')); ?></span></a>
        </li>


        <li>
            <a href="<?php echo e(route('admin.testimonial.index')); ?>"><span><?php echo e(__('Testimonials')); ?></span></a>
        </li>

        <li>
            <a href="<?php echo e(route('admin.counter.index')); ?>"><span><?php echo e(__('Counter Section')); ?></span></a>
        </li>
<li>
            <a href="<?php echo e(route('admin.service.edit_text')); ?>"><span><?php echo e(__('Edit Section Text')); ?></span></a>
        </li>

<li>
            <a href="<?php echo e(route('admin.brand.index')); ?>"><span><?php echo e(__('All Brands')); ?></span></a>
        </li>
   
<li>
            <a href="<?php echo e(route('admin.pricing.index')); ?>"><span><?php echo e(__('All Pricing Plans')); ?></span></a>
        </li>
<li>
            <a href="<?php echo e(route('admin.why-choose-us.index')); ?>"><span><?php echo e(__('Why Choose Us - All')); ?></span></a>
        </li>
		
	<li>
            <a href="<?php echo e(route('social.link.index')); ?>"><span><?php echo e(__('Social Settings')); ?></span></a>
        </li>	
		

    </ul>
</li>


<li>
    <a href="#menu10" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fa fa-language"></i><?php echo e(__('Languages')); ?>

    </a>
    <ul class="collapse list-unstyled" id="menu10" data-parent="#accordion">
        <li>
            <a href="<?php echo e(route('admin.language.index')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Language')); ?></span></a>
        </li>

        <li>
            <a href="<?php echo e(route('admin.admin_language.index')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Admin Language')); ?></span></a>
        </li>
    </ul>
</li>



  
<li>
    <a href="#general" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-cogs"></i><?php echo e(__('General Settings')); ?>

    </a>
	

	
    <ul class="collapse list-unstyled" id="general" data-parent="#accordion">
	
	
		        <li>
            <a href="<?php echo e(route('admin.generalsettings.websiteContent')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Website settings')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.generalsettings.logo')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Logo')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.languagelogo.index')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Language Base Logo')); ?></span></a>
        </li>
        <li>
            <a href="<?php echo e(route('admin.generalsettings.favicon')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Favicon')); ?></span></a>
        </li>





    </ul>
</li>




   


  
<li>
    <a href="#emails" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-at"></i><?php echo e(__('Email Settings')); ?>

    </a>
    <ul class="collapse list-unstyled" id="emails" data-parent="#accordion">
        <li><a href="<?php echo e(route('admin.email.config')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Email Configurations')); ?></span></a></li>  
    </ul>
</li>


<li>
    <a href="#seoTools" class="accordion-toggle wave-effect" data-toggle="collapse" aria-expanded="false">
        <i class="fas fa-wrench"></i><?php echo e(__('SEO Tools')); ?>

    </a>
    <ul class="collapse list-unstyled" id="seoTools" data-parent="#accordion">
        <li>
            <a href="<?php echo e(route('seo.meta.keywords')); ?>"><span><i class="fas fa-angle-double-right"></i><?php echo e(__('Website Meta Keywords')); ?></span></a>
        </li>
    </ul>
</li>







<li>
    <a href="<?php echo e(route('admin.cache.clear')); ?>" class=" wave-effect"><i class="fa fa-database"></i><?php echo e(__('Clear Cache')); ?></a>
</li>    <?php /**PATH /home/creativedesignbd/fraud.creativedesign.com.bd/project/resources/views/partial/admin-role/super.blade.php ENDPATH**/ ?>