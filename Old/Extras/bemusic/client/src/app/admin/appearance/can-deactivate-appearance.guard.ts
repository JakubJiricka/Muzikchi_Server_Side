import {Injectable} from "@angular/core";
import {CanDeactivate} from "@angular/router";
import {AppearanceComponent} from "./appearance.component";
import {AppearancePendingChanges} from "./appearance-editor/appearance-pending-changes.service";

@Injectable()
export class CanDeactivateAppearance implements CanDeactivate<AppearanceComponent> {

    constructor(private changes: AppearancePendingChanges) {}

    canDeactivate(): Promise<boolean>|boolean {
        return this.changes.canDeactivate() as any;
    }
}