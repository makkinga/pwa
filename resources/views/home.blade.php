@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Dashboard</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <button class="btn btn-info mb-5" id="enable-notifications" onclick="enableNotifications()">Enable push notifications</button>

                        <div class="form-group">
                            <input class="form-control" id="title" placeholder="Notification Title">
                        </div>

                        <div class="form-group">
                            <textarea id="body" class="form-control" placeholder="Notification body"></textarea>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-info" onclick="sendNotification()">Send Notification</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function enableNotifications() {
            askPermission();
        }

        function askPermission() {
            return new Promise(function (resolve, reject) {
                const permissionResult = Notification.requestPermission(function (result) {
                    resolve(result);
                });

                if (permissionResult) {
                    permissionResult.then(resolve, reject);
                }
            }).then(function (permissionResult) {
                if (permissionResult !== 'granted') {
                    throw new Error('We weren\'t granted permission.');
                } else {
                    subscribeUserToPush();
                }
            })
        }

        function subscribeUserToPush() {
            getSWRegistration().then(function (registration) {
                console.log(registration); // REMOVE

                const subscriptionOptions = {
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('{{ env('VAPID_PUBLIC_KEY') }}')
                };

                return registration.pushManager.subscribe(subscriptionOptions);
            }).then(function (pushSubscription) {
                console.log('Received PushSubscription:', JSON.stringify(pushSubscription)); // REMOVE
                sendSubscriptionToBackEnd(pushSubscription);

                return pushSubscription;
            })
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        function getSWRegistration() {
            return new Promise(function (resolve, reject) {
                // do a thing, possibly async, then…
                if (_registration != null) {
                    resolve(_registration);
                } else {
                    reject(Error("It broke"));
                }
            });
        }

        function sendSubscriptionToBackEnd(subscription) {
            return fetch('/api/notification-subscription/{{ auth()->user()->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Bad status code from server.');
                }
                return response.json();
            }).then(function (responseData) {
                if (!(responseData && responseData.success)) {
                    throw new Error('Bad response from server.');
                }
            });
        }


        function sendNotification() {
            let data = new FormData();
            data.append('title', document.getElementById('title').value);
            data.append('body', document.getElementById('body').value);
            let xhr = new XMLHttpRequest();
            xhr.open('POST', "{{ url('/api/send-notification/'. auth()->user()->id )}}", true);
            xhr.onload = function () {
                console.log(this.responseText);
            };
            xhr.send(data);
        }
    </script>
@endsection
