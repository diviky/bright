<?php

namespace Diviky\Bright\Livewire\Concerns;

use Illuminate\Support\Facades\Validator;

trait HandlesValidation
{
    protected function check($data, $rules = null, $messages = [], $attributes = [])
    {
        $isUsingGlobalRules = is_null($rules);

        [$rules, $messages, $attributes] = $this->providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes);

        $ruleKeysToShorten = $this->getModelAttributeRuleKeysToShorten($data, $rules);

        $data = $this->unwrapDataForValidation($data);

        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($this->withValidatorCallback) {
            call_user_func($this->withValidatorCallback, $validator);

            $this->withValidatorCallback = null;
        }

        $this->shortenModelAttributesInsideValidator($ruleKeysToShorten, $validator);

        $customValues = $this->getValidationCustomValues();

        if (!empty($customValues)) {
            $validator->addCustomValues($customValues);
        }

        if ($this->isRootComponent() && $isUsingGlobalRules) {
            $validatedData = $this->withFormObjectValidators($validator, fn () => $validator->validate(), fn ($form) => $form->validate());
        } else {
            $validatedData = $validator->validate();
        }

        $this->resetErrorBag();

        return $validatedData;
    }
}
