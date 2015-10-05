#!/usr/bin/ruby
# @Author: James Weber
# @Date:   2014-11-03 12:45:30
# @Last Modified by:   James Weber
# @Last Modified time: 2014-11-03 12:45:48


# For checking if mysql is alive

def mysql_process(function)
  result = `mysqladmin -u root ping`
  
  case function
  when :status
    return true if /mysqld is alive/im.match(result)
    return false
  when :metric
    return {"mysql" => true} if /mysqld is alive/im.match(result)
    return {"mysql" => false}
  end
end