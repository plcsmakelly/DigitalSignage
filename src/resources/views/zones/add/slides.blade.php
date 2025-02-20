@extends('frame')

@section('js-footer')
<script>
function handleEnter(e) {
	if(e.keyCode === 13){
		submit();
		e.preventDefault();
	}
}
function submit() {
swal({
  title: "Import confirmation",
  text: "Do you want to import this document into this zone?",
  icon: "info",
  buttons: {
  cancel: {
    text: "Cancel",
    value: false,
    visible: true,
    className: "",
    closeModal: true,
  },
  confirm: {
    text: "Continue",
    value: true,
    visible: true,
    className: "",
    closeModal: false
  }
},
  dangerMode: false,
  closeModal: false
})
.then((willDelete) => {
  if (willDelete) {
	document.getElementById('form').submit();
  }
});
}
</script>
@stop
@section('content')
  <header class="bg-white shadow">
<div class="lg:flex lg:items-center lg:justify-between max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
<div class="flex-1 min-w-0">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
      Import from Slides
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
    <form action="{{ url('zones/content/add/slides/'.$zone->id) }}" id="form" method="post">
    {!! csrf_field() !!}
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="shadow sm:rounded-md sm:overflow-hidden">
          <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
          @if($errors->any())
            <div class="rounded-md bg-red-100 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">{{ $errors->first() }}</h3>
                    </div>
                </div>
            </div>
            @endif
	  <div>
		<label for="file" class="block text-sm font-medium text-gray-700">
			Google Slides Presentation URL
		</label>
		<div class="mt-1">
			<input type="text" id="presentation" class="form-input focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" name="presentation" placeholder="https://docs.google.com/presentation/d/xxx/edit" onkeypress="handleEnter(event)" autofocus>
		</div>
		<p class="mt-2 text-sm text-gray-500">
			This is the edit or sharing URL that you retrieve inside Slides under the Share button.
              </p>
            </div>

          </div>
          <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
            <a href="javascript:submit()" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Start
            </a>
          </div>
        </div>
	</form>
    </div>
  </div>
</div>
    </div>
  </main>
@stop
