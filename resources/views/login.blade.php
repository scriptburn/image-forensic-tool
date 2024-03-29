@extends('default')
@section('content')
<style type="text/css">
    body {
  display: -ms-flexbox;
  display: -webkit-box;
  display: flex;
  -ms-flex-align: center;
  -ms-flex-pack: center;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-pack: center;
  justify-content: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
  text-align: center
}

.form-signin {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .checkbox {
  font-weight: 400;
}
.form-signin .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

</style>


<form class="form-signin" method="post" action="{{ route('doLogin') }}">
     @if(!empty($_SESSION['error']))
    <div class="alert alert-danger" role="alert"  >
    {{ $_SESSION['error']}}

    @php  unset($_SESSION['error'])  @endphp
                                        </div>
@endif
<div class="card mb-4 box-shadow">
    <div class="card-body"> @if(env('LOGIN_LOGO')) <img class="mb-4" src="{{ env('LOGIN_LOGO')}}" alt=""  > @endif
    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
    <label for="inputEmail" class="sr-only">Email address</label>
    <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required="" autofocus="" name="email">
    <label for="inputPassword" class="sr-only">Password</label>
    <input type="password" id="inputPassword" class="form-control" placeholder="Password" required=""  name="password">
    
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button></div>
</div>
 </form>

@endsection