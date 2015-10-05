require 'houston'

class APNS
	#Contants for apple URIs
	APPLE_PRODUCTION_GATEWAY_URI   = "apn://gateway.push.apple.com:2195"
	APPLE_PRODUCTION_FEEDBACK_URI  = "apn://feedback.push.apple.com:2196"

	APPLE_DEVELOPMENT_GATEWAY_URI  = "apn://gateway.sandbox.push.apple.com:2195"
	APPLE_DEVELOPMENT_FEEDBACK_URI = "apn://feedback.sandbox.push.apple.com:2196"

	def initialize 
		#@certificate = File.read("<filename>.pem") #development
		@certificate = File.read("<filename>.pem") #production
		@passphrase  = "..."
		#puts "Opening Connection"
		@connection  = Houston::Connection.new(APPLE_PRODUCTION_GATEWAY_URI, @certificate, @passphrase)
		
		#open the connection to the APNS service
		@connection.open

	end

	
	
	# send the push notification
	def send_notification(token, alert)
		#puts "Sending Notification"
		#token = "784cc6ab66b6e481f3653e6b676d8c26609af4bc10c080434652251342b0c2d0"
				
		notification = Houston::Notification.new(device: token)
		#notification.badge = 57
		notification.sound = "sosumi.aiff"
		#notification.custom_data = {foo: "bar"}
		
		#notification.alert = "Hello, Sir!"
		notification.alert = alert
		@connection.write(notification.message)
	end
	
	
	#close the connection when we are all done
	def close	
		#puts "Closing connection"
		@connection.close
	end

end