class WCHosts 
    
    def initialize(metric)

            # create setters and getters
            metric["value"].keys.each do |value| 
                self.create_attr(value)
            end
            
            # populate values
            metric["value"].keys.each do |value| 
                self.send("#{value}=".to_sym, metric["value"][value])
            end
            
            return self
    end


    # dynamically create setters and getters for all values
    # returned from the couch hosts metrics query
    def create_method( name, &block )
      self.class.send( :define_method, name, &block )
    end
        
    def create_attr( name )
        create_method( "#{name}=".to_sym ) { |val| 
            instance_variable_set( "@" + name, val)
        }

        create_method( name.to_sym ) { 
            instance_variable_get( "@" + name ) 
        }
    end
     
end #end class