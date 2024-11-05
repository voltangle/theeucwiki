I have not made any custom NGINX forks, this is just a funny name for an NGINX instance
that is running in front of other NGINX instance running inside docker-compose deployments
of the site. Main purpose of this thing is to route multidomain traffic, so if I access
www.euc.repair, I go to one instance, but if I access dev.euc.repair, I go to another
instance.
