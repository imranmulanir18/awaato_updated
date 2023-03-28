@extends('layouts.user_type.guest')

@section('content')

  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-75">
        <div class="container">
          <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-6 d-flex flex-column mx-auto">
              <div class="card card-plain mt-8">
                <div class="card-header pb-0 text-left bg-transparent">
                  <h3 class="font-weight-bolder text-info text-gradient">Welcome!</h3>
                  <p class="mb-0">Use these awesome forms to login or create new account in your project for free.</p>
                </div>
                <div class="card-body">

                  <form role="form text-left" method="POST" action="{{url('/sign-up-user')}}">
                  @csrf
                  <input type="hidden" name="ref_user_id" id="ref_user_id" value="">
                  <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                          <input type="text" class="form-control" placeholder="First Name" name="first_name" id="first_name" aria-label="First Name" aria-describedby="first_name" value="{{ old('first_name') }}">
                          @error('first_name')
                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                          @enderror
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                          <input type="text" class="form-control" placeholder="Last Name" name="last_name" id="last_name" aria-label="Last Name" aria-describedby="last_name" value="{{ old('last_name') }}">
                          @error('last_name')
                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                          @enderror
                        </div>
                      </div>
                    </div>
                    <div class="row">
                    <div class="col-lg-12">
                      <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" placeholder="Email" name="email" id="email" aria-label="Email" aria-describedby="email-addon" value="{{ old('email') }}">
                        @error('email')
                          <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                      <label for="sponser_id" class="form-label">Sponser ID</label>
                        <input type="text" class="form-control" placeholder="Sponser ID  e.g FX22677647" name="sponser_id" id="sponser_id" aria-label="Sponser ID" aria-describedby="sponser-id-addon" value="{{ old('sponser_id') }}">
                        @error('sponser_id')
                          <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                      <label for="sponser_name" class="form-label">Sponser Name</label>
                        <input type="text" class="form-control" placeholder="Sponser Name" name="sponser_name" id="sponser_name" aria-label="Sponser name" aria-describedby="sponser-name-addon" value="{{ old('sponser_name') }}" readonly>
                        @error('sponser_name')
                          <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                      </div>
                    </div>
                  </div>
                  <div class="row">
                      <div class="col-lg-6">
                        <div class="mb-3">
                        <label for="user_id" class="form-label">User ID</label>
                          <input type="text" class="form-control" placeholder="User ID" name="user_id" id="user_id" aria-label="User ID" aria-describedby="user_id" value="{{ old('user_id') }}" readonly>
                          @error('user_id')
                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                          @enderror
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="mb-3">
                          <label for="position" class="form-label">Position</label>
                          <select class="form-control" placeholder="Position" name="position" id="position" aria-label="Position" aria-describedby="position" >
                          <option value="">Select position</option>
                          <option value="1" {{ old('position') == '1' ? "selected" : "" }}>Left</option>
                          <option value="2" {{ old('position') == '2' ? "selected" : "" }}>Right</option>
                          </select>
                          @error('position')
                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                          @enderror
                        </div>
                      </div>
                    </div>                  
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="mb-3">
                      <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" placeholder="Password" name="password" id="password" aria-label="Password" aria-describedby="password-addon">
                        @error('password')
                          <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="mb-3">
                      <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" placeholder="Confirm Password" name="confirm_password" id="confirm_password" aria-label="Confirm Password" aria-describedby="confirm-password-addon">
                        @error('confirm_password')
                          <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                      </div>
                    </div>
                  </div>
                  <div class="form-check form-check-info text-left">
                    <input class="form-check-input" type="checkbox" name="agreement" id="flexCheckDefault" checked>
                    <label class="form-check-label" for="flexCheckDefault">
                      I agree the <a href="javascript:void(0);" class="text-dark font-weight-bolder">Terms and Conditions</a>
                    </label>
                    @error('agreement')
                      <p class="text-danger text-xs mt-2">First, agree to the Terms and Conditions, then try register again.</p>
                    @enderror
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Sign up</button>
                  </div>
                  
                </form>

                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-4 text-sm mx-auto">
                  Already have an account?
                    <a href="{{url('/sign-in')}}" class="text-info text-gradient font-weight-bold">Sign in</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6" style="background-image:url('../assets/img/curved-images/curved6.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

<script>

  $(document).ready(function(){
    //Get user ID
    getUserId();

    // Get sponser
    $(document).on('keyup','#sponser_id',function(){

      var sponser_id = $(this).val();
      $.ajaxSetup({
              headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            }); 

      $.ajax({
            type:'POST',
            url:"{{url('/get-sponser-id')}}",
            data:{sponser_id:sponser_id},
            success:function(response){
                if(response.status == '200'){
                    $('#sponser_name').val(response.data.fullname);
                    $('#ref_user_id').val(response.data.id);
                }
            }
      });
    });

  });

  function getUserId(){

      $.ajaxSetup({
              headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
            }); 

      $.ajax({
            type:'GET',
            url:"{{url('/get-user-id')}}",
            data:{},
            success:function(response){
                if(response.status == '200'){
                    $('#user_id').val(response.data);
                }
            }
      });
  }


</script>
@endsection
