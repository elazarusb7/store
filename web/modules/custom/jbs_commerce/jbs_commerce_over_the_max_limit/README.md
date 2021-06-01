# JBS Commerce Over The Limit Check

Provides functionality for storing, validating and displaying product max limit 

Installation:
Enable jbs_commerce_over_the_max_limit module

Configuration:
Next few steps only if max limit field is not part of the order type field yet.

GoTo: /admin/commerce/config/product-types/[product-type]
    and add either custom MaxLimit field or regular number field to store max limit value
GoTo: /admin/config/content/maxlimit and, if empty,
    save machine name of the max limit field you just created in the order type max limit field 