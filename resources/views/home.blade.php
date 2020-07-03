<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<link href="{{ asset('css/style.css') }}" rel="stylesheet">


<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->


<html>
<head>
<style>
    .chat_list:hover {
        background-color: #ccc;
        cursor: pointer;
    }
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" type="text/css" rel="stylesheet"

</head>
<body>
<div class="container">
<h3 class=" text-center">Messaging</h3>
<div class="messaging">
      <div class="inbox_msg">
        <div class="inbox_people">
            <div class="inbox_chat">
            <input type="hidden" id="max" value="{{ $max->id }}">
              @foreach ($users as $user)
              {{-- active_chat --}}
                <div class="chat_list" id="chat{{ $user->id }}" data-email="{{ $user->email }}">
                    <div class="chat_people">
                        <div class="chat_ib">
                            <div class="chat_img"> <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"> </div>
                            <div class="chat_ib">
                                <h4>{{ $user->name }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
               @endforeach
            </div>
        </div>
        <div class="mesgs" id="msgs">
            <div class="msg_history" id="outgoing">
                <div class="outgoing_msg">

                </div>
            </div>
            <div class="type_msg">
              <div class="input_msg_write" style="display: none" id="type_msg">
                <input type="text" class="write_msg" placeholder="Type a message" id="msg" />
                <button class="msg_send_btn" id="send" type="button"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></button>
              </div>
            </div>
      </div>
    </div>
    <input type="hidden" name="email" id="email" value="{{ Auth::User()->email }}">
  </div>
    <script src="https://media.twiliocdn.com/sdk/js/chat/v3.4/twilio-chat.min.js"></script>

    <script>
        var globalToken;
        var globalChannel;
        var currentClient;

        $('#send').click(function(){
            globalChannel.sendMessage($('#msg').val());
            $("#outgoing").animate({ scrollTop: 200000000 }, "slow");
            $('#msg').val('')
        })

        $('#msg').keydown((e)=>{
            console.log(e.keyCode)
            if(e.keyCode==13){
                globalChannel.sendMessage($('#msg').val());
                $("#outgoing").animate({ scrollTop: 200000000 }, "slow");
                $('#msg').val('')//asd
            }
        })

        function handler (message)
        {
          console.log(message)
          console.log($('#email').val())
          console.log(message.author)
          if($('#email').val()==message.author){
            $('#outgoing').append("<div class='outgoing_msg'><div class='sent_msg'><p>"+message.body+"</p><span class='time_date'>"+message.timestamp+"</span> </div></div>")
          }
          else{
            $('#outgoing').append("<div class='incoming_msg'><div class='received_msg'><div class='received_withd_msg'> <p>"+message.body+"</p><span class='time_date'>"+message.timestamp+"</span>  </div></div></div>")
          }
        }
        for (let i = 0; i < $('#max').val(); i++) {
        $(`#chat${i+1}`).click(function(){
        $("#type_msg").css({"display":"block"});
        $('#outgoing').html('')
        $.post('api/token',{identity:$('#email').val() })
        .done(function(response) {
            console.log(response.token)
            globalToken = response.token
            console.log('hello')
            Twilio.Chat.Client.create(globalToken).then(client => {
            currentClient = client;
            $.post('api/create',{email1:$('#email').val(),email2:$(`#chat${i+1}`).data('email') })
            .done(function(response) {
            console.log(response)
            client.getChannelBySid(response)
            .then(function(channel) {
            globalChannel = channel
            console.log(channel)
            console.log(currentClient)
            console.log(globalChannel)
            channel.getMessages().then(async function(messages) {
                const totalMessages = messages.items.length;
                for (let i = 0; i < totalMessages; i++) {
                const message = messages.items[i];
                if($('#email').val()==message.author){
                  $('#outgoing').append("<div class='outgoing_msg'><div class='sent_msg'><p>"+message.body+"</p><span class='time_date'>"+message.timestamp+"</span> </div></div>")
                  $("#outgoing").animate({ scrollTop: 200000000 }, "slow");
                }
                else{
                  $('#outgoing').append("<div class='incoming_msg'><div class='received_msg'><div class='received_withd_msg'> <p>"+message.body+"</p><span class='time_date'>"+message.timestamp+"</span>  </div></div></div>")
                  $("#outgoing").animate({ scrollTop: 200000000 }, "slow");
                }
                }
                });
                globalChannel.on('messageAdded',handler);
            })
                .fail(function(error) {
                   console.log('Failed to fetch the Access Token with error: ' + error);
                });
            });
            });
        })
        .fail(function(error) {
          console.log('Failed to fetch the Access Token with error: ' + error);
        });
        })
        $('#send').click(()=>{
        })
        }
    </script>
    </body>
    </html>
