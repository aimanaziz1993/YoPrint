<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>#1 Assignment - CSV Uploader | Job & Queue | Laravel Horizon</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite('resources/css/app.css')

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" 
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" 
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <link
            rel="stylesheet"
            href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css"
            type="text/css"
        />

        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

        <style>
            .dropzone {
                border: 2px dashed rgba(0,0,0,0.2);
            }
        </style>
        
    </head>
    <body>
        <div class="w-full h-screen bg-gray-100 space-y-10 py-5">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col">
                    <div class="mb-4">
                        <h1 class="text-3xl font-bolder leading-tight text-gray-500">#Yoprint Software - #01</h1>
                    </div>
                    <div class="-mb-2 py-4 flex flex-wrap flex-grow justify-between">
                        <form id="upload-form" class="dropzone w-full border-dashed" enctype="multipart/form-data">
                            @csrf
                        </form>
                    </div>
                    <div class="-my-2 py-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                        <div class="align-middle inline-block w-full shadow overflow-x-auto sm:rounded-lg border-b border-gray-200">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200 text-xs leading-4 text-gray-500 uppercase tracking-wider">
                                        <th class="px-6 py-3 text-center font-medium">
                                            #
                                        </th>
                                        <th class="px-6 py-3 text-center font-medium">
                                            Time
                                        </th>
                                        <th class="px-6 py-3 text-center font-medium">
                                            Filename
                                        </th>
                                        <th class="px-6 py-3 text-center font-medium">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                
                                <tbody id="filesBody" class="bg-white"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script>
            let batchID;
            let hasCompleted = false;
            let job;
            let ready = false;
            let batches = [];
            let hasNewEntries = false;
            var tableBody = document.getElementById('filesBody');

            Dropzone.options.uploadForm = {
                url: "{{ route('api.upload') }}",
                autoQueue: true,
                autoProcessQueue: true,
                uploadMultiple: true,
                parallelUploads: 1,
                maxFiles: 10,
                acceptedFiles: 'text/csv',
                dictDefaultMessage: "Select file / Drag and drop",
                dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
                init: function() {
                    var myDropzone = this;

                    this.on("uploadprogress", function(file, progress, bytesSent) {
                        console.log('progress', file, progress, bytesSent);
                    });

                    this.on("totaluploadprogress", function(progress) {
                        console.log('total progress', progress);
                    });

                    this.on("sendingmultiple", function(file) {
                        console.log('sendingmultiple', file)
                    });
                    this.on("successmultiple", function(files, response) {
                        console.log('successmultiple', files, response)
                        if (response.job) {
                            $('#filesBody').empty();
                            loadNewEntries();
                        }

                    });
                    this.on("errormultiple", function(files, response) {
                        console.log('errormultiple', files, response)
                    });
                }
            }

            $(document).ready(function() {
                loadBatches();
                refreshBatchesPeriodically();
            });

            function tableAppendUI(batches) {
                $('#filesBody').empty();

                var html = batches.map((b, k) => {

                    let status = "Pending";
                    let badgeClass = "bg-yellow-200 text-yellow-800";

                    if (!b.cancelledAt && b.failedJobs > 0 && b.totalJobs - b.pendingJobs < b.totalJobs) {
                        badgeClass = "bg-red-200 text-red-800";
                        status = 'Failures';
                    } else if (!b.cancelledAt && b.totalJobs - b.pendingJobs == b.totalJobs) {
                        badgeClass = "bg-green-200 text-green-800";
                        status = 'Completed';
                    } else if (b.cancelledAt) {
                        badgeClass = "bg-orange-200 text-orange-800";
                        status = 'Cancelled'
                    } else if (!b.cancelledAt && b.pendingJobs > 0 && !b.failedJobs) {
                        badgeClass = "bg-slate-200 text-slate-800";
                        status = 'Processing'
                    }

                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-center">
                                ${k + 1}
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-center">
                                ${new Date(b.createdAt).toLocaleString() }
                                &nbsp;
                                ( ${ timeSince(new Date(b.createdAt)) } ago )
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-center">
                                ${b.name || b.id}
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-center">
                                <div class="flex flex-row items-center justify-around">
                                    <div class="text-xs text-center px-3 rounded-full ${badgeClass}">
                                        ${status || ""}
                                    </div>
                                    <span id="progressRefresh" data-initial-progress="${b.progress}" class="text-xs text-center">${b.progress}%</span>
                                </div>
                            </td>
                        </tr>
                        `
                }).join("");

                $('#filesBody').append(html);
            }

            function loadBatches(beforeId = '', refreshing = false) {
                if (!refreshing) {
                    ready = false;
                }

                $.ajax({
                    type: "GET",
                    url: "{{ route('api.batches') }}" + "?before_id=" + beforeId,
                    success: function(response) {
                        if (refreshing && !response.batches.length) {
                            return;
                        }

                        if (refreshing && batches.length && response.batches[0]?.id !== batches[0]?.id) {
                            hasNewEntries = true;
                        } else {
                            batches = response.batches;
                            tableAppendUI(batches);
                        }

                        ready = true;
                    },
                    error: function(err) {
                        console.log(err)
                    }
                });

            }

            function refreshBatchesPeriodically() {
                var interval = setInterval(() => {
                    loadBatches('', true)
                }, 1500);
            }

            function loadNewEntries() {
                batches = [];
                loadBatches(0, false);
                hasNewEntries = false;
            }

            function timeSince(date) {

                var seconds = Math.floor((new Date() - date) / 1000);

                var interval = seconds / 31536000;

                if (interval > 1) {
                return Math.floor(interval) + " years";
                }
                interval = seconds / 2592000;
                if (interval > 1) {
                return Math.floor(interval) + " months";
                }
                interval = seconds / 86400;
                if (interval > 1) {
                return Math.floor(interval) + " days";
                }
                interval = seconds / 3600;
                if (interval > 1) {
                return Math.floor(interval) + " hours";
                }
                interval = seconds / 60;
                if (interval > 1) {
                return Math.floor(interval) + " minutes";
                }
                return Math.floor(seconds) + " seconds";
            }
        </script>

    </body>
</html>
