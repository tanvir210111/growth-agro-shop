@extends('layouts.admin')

@section('content')

<div class="content-area">
              <div class="mr-breadcrumb">
                <div class="row">
                  <div class="col-lg-12">
                      <h4 class="heading">{{ __('Website Contents') }}</h4>
                    <ul class="links">
                      <li>
                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                      </li>
                      <li>
                        <a href="javascript:;">{{ __('General Settings') }}</a>
                      </li>
                      <li>
                        <a href="">{{ __('Website Contents') }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="add-product-content">
                @include('includes.admin.form-both')
                <div class="row">
                  <div class="col-lg-12">
                    <div class="product-description">
                      <div class="body-area">
                      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                      <form class="uplogo-form" id="geniusform" action="{{ route('admin.generalsettings.update')}}"  method="POST" enctype="multipart/form-data">
                          {{ csrf_field() }}

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Website Title') }} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{ __('Write Your Site Title Here') }}" name="title" value="{{$data->title}}" required="">
                          </div>
                        </div>
						
						<div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Mobile Number') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{ __('Write Your Mobile Number') }}" name="phone" value="{{$data->phone}}">
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Address') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <textarea class="input-field" placeholder="{{ __('Write Your Address') }}" name="address">{{$data->address}}</textarea>
                          </div>
                        </div>

						
                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Email') }} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{ __('Write Your Email Address') }}" name="email" value="{{$data->email}}" required="">
                          </div>
                        </div>
						
						
						                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Footer Details') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <textarea class="input-field" placeholder="{{ __('Write Your Details') }}" name="footer_details">{{$data->footer_details}}</textarea>
                          </div>
                        </div>
						
						
						
						
						
						
						
						

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Primary Color') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="form-group">
                                <div class="input-group colorpicker-component cp">
                                <input type="text" class="form-control input-field color-field cp" name="theme_color" value="{{$data->theme_color}}"/>
                                  <span class="input-group-addon"><i></i></span>
                                </div>
                              </div>

                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Secondery Color') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="form-group">
                                <div class="input-group colorpicker-component cp">
                                  <input type="text" class="form-control input-field color-field cp" name="footer_color" value="{{$data->footer_color}}" />
                                  <span class="input-group-addon"><i></i></span>
                                </div>
                              </div>
                          </div>
                        </div>

                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Footer Color') }} *</h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="form-group">
                                <div class="input-group colorpicker-component cp">
                                <input type="text" class="form-control input-field color-field cp" name="copyright_color" value="{{$data->copyright_color}}"/>
                                  <span class="input-group-addon"><i></i></span>
                                </div>
                              </div>

                          </div>
                        </div>
						
						
						
						                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">
                                <h4 class="heading">{{ __('Facebook Page URL') }} *
                                  </h4>
                            </div>
                          </div>
                          <div class="col-lg-6">
                            <input type="text" class="input-field" placeholder="{{ __('Write Your Page URL') }}" name="facebook_page_link" value="{{$data->facebook_page_link}}" required="">
                          </div>
                        </div>
						 
						
						
						<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading">{{ __('SMS API Key') }} *</h4>
            <p class="sub-heading">{{ __('Get this from bulksmsbd.net') }}</p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="{{ __('Enter Your SMS API Key') }}" name="sms_api_key" value="{{$data->sms_api_key}}">
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading">{{ __('SMS Sender ID') }} *</h4>
            <p class="sub-heading">{{ __('Approved Sender ID') }}</p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="{{ __('Enter Your SMS Sender ID') }}" name="sms_sender_id" value="{{$data->sms_sender_id}}">
    </div>
</div>
						
						
						
						<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading">{{ __('BDCourier API Key') }} *</h4>
            <p class="sub-heading">{{ __('Get this from BDCourier Panel') }}</p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="{{ __('Enter Your BDCourier API Key') }}" name="bd_courier_api_key" value="{{ $data->bd_courier_api_key }}">
    </div>
</div>
				{{-- UddoktaPay API URL --}}
<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading">{{ __('UddoktaPay API URL') }} *</h4>
            <p class="sub-heading">{{ __('Get This from UddoktaPay Panel') }}</p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="{{ __('Enter UddoktaPay API URL') }}" name="uddoktapay_api_url" value="{{ $data->uddoktapay_api_url }}">
    </div>
</div>

{{-- UddoktaPay API Key --}}
<div class="row justify-content-center">
    <div class="col-lg-3">
        <div class="left-area">
            <h4 class="heading">{{ __('UddoktaPay API Key') }} *</h4>
            <p class="sub-heading">{{ __('Get this from UddoktaPay Panel') }}</p>
        </div>
    </div>
    <div class="col-lg-6">
        <input type="text" class="input-field" placeholder="{{ __('Enter UddoktaPay API Key') }}" name="uddoktapay_api_key" value="{{ $data->uddoktapay_api_key }}">
    </div>
</div>



					<div class="row justify-content-center">
                              <div class="col-lg-3">
                                <div class="left-area">
                                    <h4 class="heading">
                                        Search Console Verification Code
                                        <p class="sub-heading">{{(__('In Any Language'))}}</p>
                                    </h4>
                                  
                                </div>
                              </div>
                              <div class="col-lg-6">
                                  <div class="tawk-area">
                                  <textarea name="search_console" required="">{{$data->search_console}}</textarea>
                                  </div>
                              </div>
                            </div>
							
		

                    


                            <div class="row justify-content-center">
                            <div class="col-lg-3">
                              <div class="left-area">
                                  <h4 class="heading">{{ __('TimeZone') }} *
                                    </h4>
                              </div>
                            </div>
                            <div class="col-lg-6">
                              @php
                                $timezone_identifiers =
                                    DateTimeZone::listIdentifiers(DateTimeZone::ALL);

                                echo "<select name='time_zone'>";

                                echo "<option disabled selected>
                                        Please Select Timezone
                                      </option>";

                                $n = 419;
                                for($i = 0; $i < $n; $i++) {
                                  if($data->time_zone == $timezone_identifiers[$i]){
                                        $msg = 'selected';
                                    }else{
                                        $msg = '';
                                    }
                                    echo "<option value='" . $timezone_identifiers[$i] ."' ".$msg.">" . $timezone_identifiers[$i] . "</option>";
                                }

                                echo "</select>";
                              @endphp
                            </div>
                          </div>





                        <div class="row justify-content-center">
                          <div class="col-lg-3">
                            <div class="left-area">

                            </div>
                          </div>
                          <div class="col-lg-6">
                            <button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
                          </div>
                        </div>
                     </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

@endsection

@section('scripts')
<script src="{{asset('assets/admin/js/notify.js')}}"></script>
<script src="{{asset('assets/admin/js/distawk.js')}}"></script>

@endsection
