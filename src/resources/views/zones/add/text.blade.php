@extends('frame')

@section('content')
  <header class="bg-white shadow">
<div class="lg:flex lg:items-center lg:justify-between max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
<div class="flex-1 min-w-0">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
      Add Text
    </h2>
    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
      <div class="mt-2 flex items-center text-sm text-gray-500">
	 Adding to existing zone {{ $zone->name }}
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
        <h3 class="text-lg font-medium leading-6 text-gray-900">Media Information</h3>
        <p class="mt-1 text-sm text-gray-600">
        </p>
      </div>
    </div>
    <form action="{{ url('zones/content/add/text/'.$zone->id) }}" id="form" method="post">
    {!! csrf_field() !!}
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="shadow sm:rounded-md sm:overflow-hidden">
          <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
	  <div>
		<label for="file" class="block text-sm font-medium text-gray-700">
			Text Message
		</label>
		<div class="mt-1">
			<textarea id="text" rows="10" class="form-input focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" name="text" autofocus>
      </textarea>
		</div>
		<p class="mt-2 text-sm text-gray-500">
			Text to be shown on the display.
              </p>
            </div>

          </div>
          <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
            <input type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" value="Start" />
          </div>
        </div>
	</form>
    </div>
  </div>
</div>
    </div>
  </main>
@stop
