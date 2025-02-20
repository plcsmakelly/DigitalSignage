@extends('frame')

@section('content')
  <header class="bg-white shadow">
<div class="lg:flex lg:items-center lg:justify-between max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
<div class="flex-1 min-w-0">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
      Account Settings
    </h2>
    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
      <div class="mt-2 flex items-center text-sm text-gray-500">
	{{ $user->email }}
      </div>
    </div>
  </div>
</div>
  </header>
  <main>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

<div>
  <div class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1">
      <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Google Account Link</h3>
        <p class="mt-1 text-sm text-gray-600">
          Linking your Google account is required in order to import from Google Slides.
        </p>
      </div>
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="shadow sm:rounded-md sm:overflow-hidden">
          <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
            <div class="grid grid-cols-3 gap-6">
              <div class="col-span-3 sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">
                  Google Link Status
                </label>
                <div class="mt-1 flex rounded-md shadow-sm">
			@if($token->isCorrectlyLinked())
			<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  	Account linked OK
                	</span>
			@else
			<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Account not linked!
                        </span>
			@endif
                </div>
              </div>
            </div>

	  <div>
		<label for="type" class="block text-sm font-medium text-gray-700">
			Google Account Email
		</label>
		<div class="mt-1">
			{{ $token->google_email }}
		</div>
            </div>

	<div>
                <label for="type" class="block text-sm font-medium text-gray-700">
                        Link Account
                </label>
                <div class="mt-1">
			            <a href="{{ url('google/link') }}"><img src="{{ asset('assets/images/btn_google_signin_light_normal_web@2x.png') }}" class="h-12 w-auto"></a>
                </div>
            </div>

          </div>
        </div>
    </div>
  </div>
</div>
    </div>
  </main>
@stop
