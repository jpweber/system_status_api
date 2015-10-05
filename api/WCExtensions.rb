#!/usr/bin/ruby
# @Author: James Weber
# @Date:   2014-11-03 12:16:28
# @Last Modified by:   James Weber
# @Last Modified time: 2014-11-03 15:22:41

# load up the modules that are in the enabled-ext dir
# this how we do dynamic modules

module Extensions
  class << self

    EXTENSION_PATH = "/usr/src/watchcat/extensions/"

    def init
      extensions = get_files(EXTENSION_PATH)
      #puts extensions   # => array
      extensions.each do | ext |
        ext_load(ext)
      end
    end

    def get_files(path)
      begin
        Dir.chdir(path)
        files =  Dir.glob("*.rb")
      rescue
        puts "Could not open #{path}"
      end
        return files
    end

    def ext_load(ext)
      puts "Loading #{ext} Extension"
      require_relative(EXTENSION_PATH + ext)
    end
  end
end

Extensions.init



