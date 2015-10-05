require_relative 'Apns.rb'

apns = APNS.new

results = apns.send_notification("UUID Here", "Hello my name is david")
puts results

results = apns.close 
puts results